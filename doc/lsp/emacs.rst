Emacs
=====

Client Guides
-------------


.. tabs::

    .. tab:: LSP Mode

        Install Phpactor with :ref:`installation_global` then install `LSP Mode
        <https://github.com/emacs-lsp/lsp-mode>`_.  Please read `Installation - LSP Mode
        <https://emacs-lsp.github.io/lsp-mode/page/installation/>`_ for client installation.
        Installing ``lsp-ui`` in addition to lsp-mode integrates a rich UI for LSP into Emacs.

        For example: include it in your ``init.el``

        ::

            ;; Add lsp or lsp-deferred function call to functions for your php-mode customization
            (defun init-php-mode ()
              (lsp-deferred))

            (with-eval-after-load 'php-mode
              ;; If phpactor command is not installed as global, write the full path
              ;; (custom-set-variables '(lsp-phpactor-path "/path/to/phpactor"))
              (add-hook 'php-mode-hook #'init-php-mode))

        If you're using use-package or leaf.el, you can add it to the ``:hook`` or ``:init`` clauses
        of those blocks instead of ``with-eval-after-load``.

        Read `FAQ - LSP Mode
        <https://emacs-lsp.github.io/lsp-mode/page/faq/#i-have-multiple-language-servers-for-language-foo-and-i-want-to-select-the-server-per-project-what-can-i-do>`_
        if you have a language server other than Phpactor in your environment.

    .. tab:: Eglot

        Install Phpactor with :ref:`installation_global` then install `Eglot
        <https://github.com/joaotavora/eglot>`_.  Eglot installs in your Emacs by executing
        ``M-x package-install eglot`` commands.

        For example: include it in your ``init.el``

        ::

            ;; Add lsp or lsp-deferred function call to functions for your php-mode customization
            (defun init-php-mode ()
              (eglot-ensure))

            (with-eval-after-load 'php-mode
              ;; If phpactor command is not installed as global, remove next ;; and write the full path
              ;; (custom-set-variables '(lsp-phpactor-path "/path/to/phpactor"))
              (add-hook 'php-mode-hook #'init-php-mode))

        If you're using use-package or leaf.el, you can add it to the ``:hook`` or ``:init`` clauses
        of those blocks instead of ``with-eval-after-load``.

    .. tab:: lsp-bridge

        Install Phpactor with :ref:`installation_global` then install `lsp-bridge
        <https://github.com/manateelazycat/lsp-bridge>`_.  Please read README for client installation.

        For example: include it in your ``init.el``

        ::

            ;;; When enabled in all major modes supported by lsp-bridge
            ;; (global-lsp-bridge-mode)

            ;;; When enabling lsp-bridge only for PHP Mode

            ;; Add lsp or lsp-deferred function call to functions for your php-mode customization
            (defun init-php-mode ()
              (lsp-bridge-mode +1))

            (with-eval-after-load 'php-mode
              (custom-set-variables '(lsp-bridge-php-lsp-server . "phpactor"))
              (add-hook 'php-mode-hook #'init-php-mode))

        If you're using use-package or leaf.el, you can add it to the ``:hook`` or ``:init`` clauses
        of those blocks instead of ``with-eval-after-load``.
