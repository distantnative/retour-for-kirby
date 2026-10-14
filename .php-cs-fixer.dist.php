<?php

return Kirby\PhpCs\Config::create()->setFinder(
	PhpCsFixer\Finder::create()
		->exclude(['node_modules', 'vendor'])
		->in(__DIR__)
);
