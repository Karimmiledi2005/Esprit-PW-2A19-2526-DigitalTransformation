<?php
$dir = __DIR__;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        if (substr($content, 0, 3) === pack('CCC', 0xef, 0xbb, 0xbf)) {
            echo "Removing BOM from " . $file->getFilename() . "\n";
            file_put_contents($file->getRealPath(), substr($content, 3));
        }
    }
}
?>
