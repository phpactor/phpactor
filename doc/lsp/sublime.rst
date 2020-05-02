.. _lsp_client_sublime:

Sublime Text
============

Install Phpactor with :ref:`installation_global` then navigate to `Preferences
> Package Settings > LSP > Settings` and add the Phpactor language server
configuration as follows:

.. code:: javascript

    "clients":
	{
		"phpactor":
		{
			"command":
			[
				"phpactor",
				"language-server",
				
			],
			"enabled": true,
			"languageId": "php",
			"scopes":
			[
				"source.php",
				"embedding.php"
			],
			"syntaxes":
			[
				"Packages/PHP/PHP.sublime-syntax"
			]
		}
    }
