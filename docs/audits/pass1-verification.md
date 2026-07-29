# Pass 1 Verification Record

## Local red/green evidence

The architecture tests were written before the implementation changes.

### Source baseline test

Initial failure:

```text
Missing required file: tools/titan-zero-audit/baseline.php
Baseline audit output has not been generated.
```

After implementing the auditor:

```text
Titan Zero baseline: required=ok extensions=104/104 duplicate-symbols=150 oversized=10 missing-composer-paths=0 missing-npm-files=0
Source baseline test passed.
```

### Dependency input test

Initial failure:

```text
Missing npm file dependency rt-client: ./rt-client-0.4.7.tgz
```

The tarball was absent from all available project archives. Source search confirmed that no application module imports `rt-client`; the realtime frontend imports the in-repository `NativeRTClient` implementation. The stale package reference was removed from `package.json`, and the lockfile repair is performed idempotently by `tools/titan-zero-audit/pass1-lockfile-repair.mjs`.

After the repair:

```text
Dependency input test passed.
package JSON valid
npm pkg get valid JSON
```

## Environment limitation

The local execution environment does not include Composer and lacks the PHP DOM, XML, XMLWriter and mbstring extensions required by PHPUnit. The tests remain PHPUnit-compatible but were also executed directly as standalone PHP checks. The real Composer, PHPUnit and npm installation verification is delegated to `.github/workflows/pass1-baseline.yml` on the isolated review PR.
