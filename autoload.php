<?php

spl_autoload_register(function ($class)
{
	if (strncmp($class, 'SQL\\', 4) === 0)
	{
		$file = stream_resolve_include_path(
			'classes'.DIRECTORY_SEPARATOR
			.str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class)
			.'.php'
		);

		if ($file)
		{
			require $file;
		}
	}
});
