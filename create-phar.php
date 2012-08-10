<?php

$pharFile = "kpd.phar";

$phar = new Phar($pharFile, 0, $pharFile);
$phar->compressFiles(Phar::GZ);
$phar->setSignatureAlgorithm(Phar::SHA1);

$sourceLocation = "./";

$dirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceLocation));
foreach($dirIterator as $file) {
    if ( strpos($file->getPath(), ".git") === false && $file->getFilename() != "." 
        && $file->getFilename() != ".."
        && $file->getFilename() != "README.md" ) {
        $fileIndex = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
        $fileName = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
        $files[$fileIndex] = $fileName;
    }
}

$phar->startBuffering();
//$phar->buildFromIterator(new ArrayIterator($files));
$phar->buildFromDirectory($sourceLocation, '/\.(php|ini)$/');
$phar->stopBuffering();

$phar->setStub($phar->createDefaultStub("index.php"));