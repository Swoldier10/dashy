<?php

namespace App\Domains\Chat\Services;

use Illuminate\Support\HtmlString;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Safe markdown renderer for chat messages.
 *
 * Uses CommonMark + GFM (tables, fenced code blocks, autolinks). Raw HTML in
 * the source is escaped, so user-controlled content cannot inject markup.
 */
final class MarkdownRenderer
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new AutolinkExtension);

        $this->converter = new MarkdownConverter($environment);
    }

    public function render(string $markdown): HtmlString
    {
        return new HtmlString((string) $this->converter->convert($markdown));
    }
}
