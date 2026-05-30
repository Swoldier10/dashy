<?php

namespace Tests\Feature\Architecture;

use Illuminate\Support\Facades\File;
use SplFileInfo;
use Tests\TestCase;

/**
 * Lightweight, file-scanning guards for the load-bearing CLAUDE.md rules.
 * They lock in the layering invariants so a future change can't silently
 * reintroduce a Rule 1/2 violation (Eloquent in a Service/UI/tool, a
 * cross-domain Action import, or an Action that grew a second public method).
 */
class ArchitectureRulesTest extends TestCase
{
    /** @return list<string> */
    private function phpFiles(string $relativeDir): array
    {
        $dir = base_path($relativeDir);
        if (! File::isDirectory($dir)) {
            return [];
        }

        return array_map(
            fn (SplFileInfo $f): string => $f->getPathname(),
            array_filter(File::allFiles($dir), fn (SplFileInfo $f): bool => $f->getExtension() === 'php'),
        );
    }

    private const ELOQUENT_QUERY_PATTERNS = [
        '/::query\(/',
        '/\bDB::(table|select|insert|update|delete|statement|raw)\(/',
        // Relationship-query terminals: a relation accessor immediately reduced
        // to a query result (the exact Rule 2 leak fixed in Phase 1).
        '/->(members|teams|tasks|statuses|projects|assignees|owners|messages|entries)\(\)\s*->(exists|get|pluck|count|first|delete|update)\(/',
        '/->(save|forceFill)\(/',
    ];

    /**
     * @param  list<string>  $files
     * @return array<string, string> file => offending snippet
     */
    private function scan(array $files, array $patterns): array
    {
        $hits = [];
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $contents, $m) === 1) {
                    $hits[$file] = $m[0];
                    break;
                }
            }
        }

        return $hits;
    }

    public function test_services_do_not_execute_eloquent_directly(): void
    {
        $hits = $this->scan($this->phpFiles('app/Domains'), self::ELOQUENT_QUERY_PATTERNS);
        // Only the Services layer is under test here; Actions legitimately query.
        $serviceHits = array_filter($hits, fn ($_, $f): bool => str_contains($f, '/Services/'), ARRAY_FILTER_USE_BOTH);

        $this->assertSame([], $serviceHits, 'Services must route all DB access through Actions: '.json_encode($serviceHits, JSON_PRETTY_PRINT));
    }

    public function test_livewire_components_do_not_touch_the_database(): void
    {
        // The grandfathered Fortify logout action is exempt (kit code).
        $files = array_filter(
            $this->phpFiles('app/Livewire'),
            fn (string $f): bool => ! str_ends_with($f, 'Actions/Logout.php'),
        );

        $hits = $this->scan($files, self::ELOQUENT_QUERY_PATTERNS);

        $this->assertSame([], $hits, 'Livewire components must call domain Services, not the DB: '.json_encode($hits, JSON_PRETTY_PRINT));
    }

    public function test_ai_tools_do_not_touch_the_database(): void
    {
        $hits = $this->scan($this->phpFiles('app/Domains/Chat/Ai/Tools'), self::ELOQUENT_QUERY_PATTERNS);

        $this->assertSame([], $hits, 'AI tools must delegate persistence/reads to domain Services: '.json_encode($hits, JSON_PRETTY_PRINT));
    }

    public function test_no_domain_imports_another_domains_actions(): void
    {
        $offenders = [];
        foreach ($this->phpFiles('app/Domains') as $file) {
            // The file's own domain, e.g. "Tasks" from app/Domains/Tasks/...
            if (preg_match('#/app/Domains/([^/]+)/#', $file, $m) !== 1) {
                continue;
            }
            $ownDomain = $m[1];
            $contents = (string) file_get_contents($file);

            if (preg_match_all('/use App\\\\Domains\\\\([A-Za-z]+)\\\\Actions\\\\/', $contents, $matches) > 0) {
                foreach ($matches[1] as $importedDomain) {
                    if ($importedDomain !== $ownDomain) {
                        $offenders[] = basename($file).' imports '.$importedDomain.' Actions';
                    }
                }
            }
        }

        $this->assertSame([], $offenders, 'Cross-domain coordination must go through public Services, never another domain\'s Actions: '.json_encode($offenders, JSON_PRETTY_PRINT));
    }

    public function test_every_action_exposes_only_a_single_execute_method(): void
    {
        $offenders = [];
        foreach ($this->phpFiles('app/Domains') as $file) {
            if (! str_contains($file, '/Actions/')) {
                continue;
            }
            $contents = (string) file_get_contents($file);
            preg_match_all('/public function (\w+)\(/', $contents, $matches);
            $publicMethods = array_values(array_diff($matches[1], ['__construct']));

            if ($publicMethods !== ['execute']) {
                $offenders[] = basename($file).': ['.implode(', ', $publicMethods).']';
            }
        }

        $this->assertSame([], $offenders, 'Each Action must expose exactly one public execute() method: '.json_encode($offenders, JSON_PRETTY_PRINT));
    }
}
