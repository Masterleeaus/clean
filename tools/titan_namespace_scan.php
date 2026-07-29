<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$scanRoots = ['app'];
$files = [];

foreach ($scanRoots as $scanRoot) {
    $directory = $root . '/' . $scanRoot;
    if (! is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);
$declarations = [];
$imports = [];

$declarationTokens = [T_CLASS, T_INTERFACE, T_TRAIT];
if (defined('T_ENUM')) {
    $declarationTokens[] = T_ENUM;
}

foreach ($files as $file) {
    $source = (string) file_get_contents($file);
    $tokens = token_get_all($source);
    $namespace = '';
    $tokenCount = count($tokens);

    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];
        if (! is_array($token)) {
            continue;
        }

        if ($token[0] === T_NAMESPACE) {
            $parts = [];
            for ($cursor = $index + 1; $cursor < $tokenCount; $cursor++) {
                $candidate = $tokens[$cursor];
                if ($candidate === ';' || $candidate === '{') {
                    break;
                }
                if (is_array($candidate) && in_array($candidate[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
                    $parts[] = $candidate[1];
                }
            }
            $namespace = trim(implode('', $parts), '\\');
            continue;
        }

        if (! in_array($token[0], $declarationTokens, true)) {
            continue;
        }

        $previousMeaningful = null;
        for ($cursor = $index - 1; $cursor >= 0; $cursor--) {
            $candidate = $tokens[$cursor];
            if (is_array($candidate) && in_array($candidate[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $previousMeaningful = $candidate;
            break;
        }
        if (is_array($previousMeaningful) && $previousMeaningful[0] === T_NEW) {
            continue;
        }

        for ($cursor = $index + 1; $cursor < $tokenCount; $cursor++) {
            $candidate = $tokens[$cursor];
            if (is_array($candidate) && $candidate[0] === T_STRING) {
                $name = $namespace === '' ? $candidate[1] : $namespace . '\\' . $candidate[1];
                $declarations[$name] = ltrim(str_replace($root, '', $file), '/');
                break;
            }
            if (is_string($candidate) && $candidate === '{') {
                break;
            }
        }
    }

    if (preg_match_all('/^\s*use\s+([^;]+);/mi', $source, $matches) !== false) {
        foreach ($matches[1] as $statement) {
            $statement = trim((string) $statement);
            if (preg_match('/^(function|const)\s+/i', $statement) === 1) {
                continue;
            }

            $candidates = [];
            if (str_contains($statement, '{') && str_ends_with($statement, '}')) {
                [$prefix, $group] = explode('{', $statement, 2);
                $prefix = rtrim(trim($prefix), '\\');
                $group = rtrim($group, '}');
                foreach (explode(',', $group) as $item) {
                    $candidates[] = $prefix . '\\' . trim($item);
                }
            } else {
                $candidates = array_map('trim', explode(',', $statement));
            }

            foreach ($candidates as $candidate) {
                $candidate = preg_replace('/\s+as\s+[A-Za-z_][A-Za-z0-9_]*$/i', '', $candidate) ?? $candidate;
                $candidate = ltrim(trim($candidate), '\\');
                if (str_starts_with($candidate, 'App\\')) {
                    $imports[$candidate][] = ltrim(str_replace($root, '', $file), '/');
                }
            }
        }
    }
}

$unresolved = [];
foreach ($imports as $import => $usedBy) {
    if (! isset($declarations[$import])) {
        $unresolved[$import] = array_values(array_unique($usedBy));
    }
}

ksort($unresolved);
printf(
    "Titan namespace scan: %d PHP files, %d declarations, %d App imports, %d unresolved\n",
    count($files),
    count($declarations),
    count($imports),
    count($unresolved),
);

foreach ($unresolved as $import => $usedBy) {
    fwrite(STDERR, "UNRESOLVED {$import} used by " . implode(', ', $usedBy) . "\n");
}

exit($unresolved === [] ? 0 : 1);
