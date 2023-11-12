NVIM LSP Configuration Snippets
===============================

This page contains some useful configuration snippets which are not guaranteed
to work.

Progress Notifications
----------------------

Neovim does not support LSP progress notifications out-of-the-box.

Install a dedicated plugin such as `fidget <https://github.com/j-hui/fidget.nvim>`_

.. _nvim_configuration_snippet_commands:

Phpactor Commands
-----------------

.. note::

   This snippet depends on the "plenary" plugin (which is also required by
   "telescope")

This configuration snippet enables the following commands:

- ``:LspPhpactorReindex``: Reindex the current project
- ``:LspPhpactorStatus``: Show some useful information and statistics
- ``:LspPhpactorConfig``: Show the config in a floating window

If you want to profile Phpactor for debugging purposes:

- ``:LspPhpactorBlackfireStart``: Start :ref:`developing_blackfire_profiling` (if enabled with :ref:`param_blackfire.enabled`)
- ``:LspPhpactorBlackfireFinish``: Finish profiling and get the profiling URL

.. code-block:: lua

    -- requires plenary (which is required by telescope)
    local Float = require "plenary.window.float"

    vim.cmd([[
        augroup LspPhpactor
          autocmd!
          autocmd Filetype php command! -nargs=0 LspPhpactorReindex lua vim.lsp.buf_notify(0, "phpactor/indexer/reindex",{})
          autocmd Filetype php command! -nargs=0 LspPhpactorConfig lua LspPhpactorDumpConfig()
          autocmd Filetype php command! -nargs=0 LspPhpactorStatus lua LspPhpactorStatus()
          autocmd Filetype php command! -nargs=0 LspPhpactorBlackfireStart lua LspPhpactorBlackfireStart()
          autocmd Filetype php command! -nargs=0 LspPhpactorBlackfireFinish lua LspPhpactorBlackfireFinish()
        augroup END
    ]])

    local function showWindow(title, syntax, contents)
        local out = {};
        for match in string.gmatch(contents, "[^\n]+") do
            table.insert(out, match);
        end

        local float = Float.percentage_range_window(0.6, 0.4, { winblend = 0 }, {
            title = title,
            topleft = "┌",
            topright = "┐",
            top = "─",
            left = "│",
            right = "│",
            botleft = "└",
            botright = "┘",
            bot = "─",
        })

        vim.api.nvim_buf_set_option(float.bufnr, "filetype", syntax)
        vim.api.nvim_buf_set_lines(float.bufnr, 0, -1, false, out)
    end

    function LspPhpactorDumpConfig()
        local results, _ = vim.lsp.buf_request_sync(0, "phpactor/debug/config", {["return"]=true})
        for _, res in pairs(results or {}) do
            pcall(showWindow, 'Phpactor LSP Configuration', 'json', res['result'])
        end
    end
    function LspPhpactorStatus()
        local results, _ = vim.lsp.buf_request_sync(0, "phpactor/status", {["return"]=true})
        for _, res in pairs(results or {}) do
            pcall(showWindow, 'Phpactor Status', 'markdown', res['result'])
        end
    end

    function LspPhpactorBlackfireStart()
        local _, _ = vim.lsp.buf_request_sync(0, "blackfire/start", {})
    end
    function LspPhpactorBlackfireFinish()
        local _, _ = vim.lsp.buf_request_sync(0, "blackfire/finish", {})
    end
