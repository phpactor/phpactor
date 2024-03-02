.. _lsp_client_helix:

Helix Editor
============

Install Phpactor with :ref:`installation_global` then add the
Phpactor language server configuration in your `languages.toml`
as follows:

.. code:: toml

	# in <config_dir>/helix/languages.toml

	[[language]]
	name = "php"
	scope = "source.php"
	injection-regex = "php"
	file-types = ["php", "inc", "php4", "php5", "phtml", "ctp"]
	shebangs = ["php"]
	roots = ["composer.json", "index.php"]
	comment-token = "//"
	language-servers = [ "phpactor" ]
	indent = { tab-width = 4, unit = "    " }
