# How to use

    // INCLUDE COMPRESSOR CLASS
    include('yuicompressor.php');

    // INVOKE CLASS
    $yui = new YUICompressor(JAR_PATH, TEMP_FILES_DIR, $options);

    // ADD FILES
    $yui->addFile($absolute_path_to_file);

    // ADD STRING
    $yui->addString($string);

    // COMPRESS
    $code = $yui->compress();