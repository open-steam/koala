#!/usr/bin/php
<?php

$pathBase = __DIR__ . "/../";

passthru("rm -Rfv $pathBase/platforms/bid/cache/*");
passthru("rm -Rfv $pathBase/platforms/bid/temp/*");
passthru("rm -Rfv $pathBase/platforms/elab/cache/*");
passthru("rm -Rfv $pathBase/platforms/elab/temp/*");
passthru("rm -Rfv $pathBase/platforms/schulen-gt/cache/*");
passthru("rm -Rfv $pathBase/platforms/schulen-gt/temp/*");
