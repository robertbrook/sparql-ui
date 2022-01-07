<?php
    /**
     * Convert RDF from one format to another
     *
     * The source RDF data can either be fetched from the web
     * or typed into the Input box.
     *
     * The first thing that this script does is make a list the names of the
     * supported input and output formats. These options are then
     * displayed on the HTML form.
     *
     * The input data is loaded or parsed into an EasyRdf\Graph.
     * That graph is than outputted again in the desired output format.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    require_once "./vendor/autoload.php";
    require_once "html_tag_helpers.php";

    $input_format_options = array('Guess' => 'guess');
    $output_format_options = array();
    foreach (\EasyRdf\Format::getFormats() as $format) {
        if ($format->getSerialiserClass()) {
            $output_format_options[$format->getLabel()] = $format->getName();
        }
        if ($format->getParserClass()) {
            $input_format_options[$format->getLabel()] = $format->getName();
        }
    }

    // Stupid PHP :(
    if (isset($_REQUEST['data'])) {
        $_REQUEST['data'] = stripslashes($_REQUEST['data']);
    }

    // Default to Guess input and Turtle output
    if (!isset($_REQUEST['output_format'])) {
        $_REQUEST['output_format'] = 'turtle';
    }
    if (!isset($_REQUEST['input_format'])) {
        $_REQUEST['input_format'] = 'guess';
    }

    // Display the form, if raw option isn't set
    if (!isset($_REQUEST['raw'])) {
        print "<html>\n";
        print "<head><title>EasyRdf Converter</title><link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/water.css@2/out/water.css\">\n";
        print '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.0/codemirror.min.js" integrity="sha512-JpMCZgesTWh1iu/8ujURbwkJBgbgFWe3sTNCHdIuEvPwZuuN0nTUr2yawXahpgdEK7FOZUlW254Rp7AyDYJdjg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
        print '<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.0/codemirror.js" integrity="sha512-dK6guy/5KfuGFyZqGjtWr1HH8AGkI9UGZKD0uB9EDivJHt3dLSDgTteU0lsY4HtYbi3YhYnoKWQ5EfPS9TRCDg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
        print "</head>\n";
        print "<body>\n";
        print "<h1>EasyRdf Converter</h1>\n";

        print "<div>\n";
        print form_tag();
        print label_tag('data', 'Input Data: ').'<br />'.text_area_tag('data', '', array('cols'=>80, 'rows'=>10)) . "<br />\n";
        print label_tag('uri', 'or Uri: ').text_field_tag('uri', 'http://danbri.org/foaf.rdf#danbri', array('size'=>80)) . "<br />\n";
        print label_tag('input_format', 'Input Format: ').select_tag('input_format', $input_format_options) . "<br />\n";
        print label_tag('output_format', 'Output Format: ').select_tag('output_format', $output_format_options) . "<br />\n";
        print label_tag('raw', 'Raw Output: ').check_box_tag('raw') . "<br />\n";
        print reset_tag() . submit_tag();
        print form_end_tag();
        print "</div>\n";
    }

    if (isset($_REQUEST['uri']) or isset($_REQUEST['data'])) {
        // Parse the input
        $graph = new \EasyRdf\Graph($_REQUEST['uri']);
        if (empty($_REQUEST['data'])) {
            $graph->load($_REQUEST['uri'], $_REQUEST['input_format']);
        } else {
            $graph->parse($_REQUEST['data'], $_REQUEST['input_format'], $_REQUEST['uri']);
        }

        // Lookup the output format
        $format = \EasyRdf\Format::getFormat($_REQUEST['output_format']);

        // Serialise to the new output format
        $output = $graph->serialise($format);
        if (!is_scalar($output)) {
            $output = var_export($output, true);
        }

        // Send the output back to the client
        if (isset($_REQUEST['raw'])) {
            header('Content-Type: '.$format->getDefaultMimeType());
            print $output;
        } else {
            // print '<pre>'.htmlspecialchars($output).'</pre>';
            print '<textarea id="myTextarea">'.htmlspecialchars($output).'</textarea>';
        }
    }

print "<script>var editor = CodeMirror.fromTextArea(myTextarea, {lineNumbers: false});</script>";

    if (!isset($_REQUEST['raw'])) {
        print "</body>\n";
        print "</html>\n";
    }
