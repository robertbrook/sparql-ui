<?php


    /**
     * Display the contents of a graph
     *
     * Data from the chosen URI is loaded into an EasyRdf\Graph object.
     * Then the graph is dumped and printed to the page using the
     * $graph->dump() method.
     *
     * The call to preg_replace() replaces links in the page with
     * links back to this dump script.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2014 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    require_once "./vendor/autoload.php";
    require_once "html_tag_helpers.php";
?>
<html>
<head><title>EasyRdf Graph Dumper</title>
    <meta name="viewport" content="width=device-width">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    </head>
<body>
<h1>EasyRdf Graph Dumper</h1>

<div>
  <?= form_tag() ?>
  URI: <?= text_field_tag('uri', 'https://api.parliament.uk/query/resource?uri=http%3A%2F%2Fwww.w3.org%2F2002%2F07%2Fowl%23Class', array('size'=>80)) ?><br />
  Format: <?= label_tag('format_html', 'HTML').' '.radio_button_tag('format', 'html', true) ?>
          <?= label_tag('format_text', 'Text').' '.radio_button_tag('format', 'text') ?><br />

  <?= submit_tag() ?>
  <?= form_end_tag() ?>
</div>

<?php
    if (isset($_REQUEST['uri'])) {
        $graph = \EasyRdf\Graph::newAndLoad($_REQUEST['uri']);
        if ($graph) {
            if (isset($_REQUEST['format']) && $_REQUEST['format'] == 'text') {
                print "<pre>".$graph->dump('text')."</pre>";
            } else {
                $dump = $graph->dump('html');
                print preg_replace_callback("/ href='([^#][^']*)'/", 'makeLinkLocal', $dump);
            }
        } else {
            print "<p>Failed to create graph.</p>";
        }
    }

    # Callback function to re-write links in the dump to point back to this script
    function makeLinkLocal($matches)
    {
        $href = $matches[1];
        return " href='?uri=".urlencode($href)."#$href'";
    }
?>
</body>
</html>
