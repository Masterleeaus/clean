<?php

declare(strict_types=1);

return static function (): void {
    $root = dirname(__DIR__, 4);
    foreach ([
        'SearchBusinessesTool', 'GetSearchTool', 'CancelSearchTool', 'ExportSearchTool', 'ListCandidatesTool', 'MatchCandidateTool',
        'ClassifyCandidateTool', 'ApproveCandidateTool', 'RejectCandidateTool', 'PromoteCandidateTool',
        'AnalyseTerritoryTool', 'ReadUsageTool',
    ] as $tool) {
        $file = $root.'/app/Extensions/TitanMapsIntelligence/Tools/'.$tool.'.php';
        assert_true(is_file($file), 'Missing tool '.$tool);
        $content = (string) file_get_contents($file);
        assert_true(str_contains($content, 'AuthorisedCompanyContext'));
        assert_true(str_contains($content, 'PermissionAuthorizer'));
        assert_true(! str_contains($content, 'raw_payload'));
    }
};
