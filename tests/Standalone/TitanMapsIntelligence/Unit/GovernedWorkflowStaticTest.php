<?php

declare(strict_types=1);

return static function (): void {
    $root = dirname(__DIR__, 4);
    $controller = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/Http/Controllers/CandidateController.php');
    assert_true(str_contains($controller, 'CandidateReviewService'));
    assert_true(str_contains($controller, 'CandidateMatchWorkflow'));
    assert_true(! str_contains($controller, 'forceFill('), 'Controllers must delegate governed candidate mutations');

    foreach (['ClassifyCandidateTool', 'ApproveCandidateTool', 'RejectCandidateTool'] as $tool) {
        $content = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/Tools/'.$tool.'.php');
        assert_true(str_contains($content, 'CandidateReviewService'));
        assert_true(! str_contains($content, 'forceFill('));
    }
    $match = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/Tools/MatchCandidateTool.php');
    assert_true(str_contains($match, 'CandidateMatchWorkflow'));
    assert_true(! str_contains($match, 'WorkCoreCandidateLookup'));
    assert_true(! str_contains($match, 'CandidateMatchingService'));

    $territoryController = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/Http/Controllers/TerritoryController.php');
    $territoryTool = (string) file_get_contents($root.'/app/Extensions/TitanMapsIntelligence/Tools/AnalyseTerritoryTool.php');
    assert_true(str_contains($territoryController, 'TerritoryAnalysisManager'));
    assert_true(str_contains($territoryTool, 'TerritoryAnalysisManager'));
};
