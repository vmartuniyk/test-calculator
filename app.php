<?php

require 'vendor/autoload.php';


try {
    if (!isset($argv[1])) {
        throw new InvalidArgumentException("Please provide the path to the input file as an argument.");
    }

    $filePath = $argv[1];

    if (!file_exists($filePath)) {
        throw new InvalidArgumentException("The file $filePath does not exist.");
    }

    $processor = new TransactionProcessor();
    $processor->processFile($filePath);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
