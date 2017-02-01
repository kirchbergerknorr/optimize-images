#!/usr/bin/php
<?php

require 'vendor/autoload.php';

function optimizeImage($filepath)
{
    $factory = new \ImageOptimizer\OptimizerFactory();
    $optimizer = $factory->get();
    $optimizer->optimize($filepath);
}

function recurseImages($dir, &$initialBytes, &$bytesSaved)
{
    $images = $initialBytes = $bytesSaved = 0;
    $stack[] = $dir;

    while ($stack) {
        sort($stack);
        $thisdir = array_pop($stack);
        if ($dircont = scandir($thisdir)) {
            for ($i = 0; isset($dircont[$i]); $i++) {
                if ($dircont[$i]{0} !== '.') {
                    $current_file = $thisdir .'/'. $dircont[$i];
                    if (!is_link($current_file)) {
                        if (is_dir($current_file)) {
                            $stack[] = $current_file;
                        } else {
                            $initialBytes += filesize($current_file);
                            optimizeImage($current_file);
                            $bytesSaved += filesize($current_file);
                            $images++;
                        }
                    }
                }
            }
        }
    }
    return $images;
}

if (is_dir($argv[1])) {
    $time = time();
    $initialBytes = $bytesSaved = 0;
    $path = realpath($argv[1]);

    $images = recurseImages($path, $initialBytes, $bytesSaved);

    $time = time() - $time;
    echo "Time: {$time}s\tImages: $images\tInitial bytes: $initialBytes\tBytes saved: $bytesSaved (". round($bytesSaved / $initialBytes * 100, 1) ."%)\n";
}
