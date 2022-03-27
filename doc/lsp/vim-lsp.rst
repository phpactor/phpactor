NVIM LSP Configuration Snippets
===============================

This page contains some useful configuration snippets which are not guaranteed
to work.

Phpactor Commands
-----------------

.. note::

   This snippet depends on the "plenary" plugin (which is also required by
   "telescope")

This configuration snippet enables the following commands:

- ``:LspPhpactorReindex``: Reindex the current project
- ``:LspPhpactorStatus``: Show some useful information and statistics
- ``:LspPhpactorConfig``: Show the config in a floating window

.. code-block:: lua

    -- requires plenary (which is required by telescope)
    local Float = require "plenary.window.float"

    vim.cmd([[
    :command! -nargs=0 LspPhpactorReindex lua vim.lsp.buf_notify(0, "phpactor/indexer/reindex",{})
    :command! -nargs=0 LspPhpactorConfig lua LspPhpactorDumpConfig()
    :command! -nargs=0 LspPhpactorStatus lua LspPhpactorStatus()
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
            showWindow("Phpactor LSP Configuration", "json", res["result"])
        end
    end
    function LspPhpactorStatus()
        local results, _ = vim.lsp.buf_request_sync(0, "phpactor/status", {["return"]=true})
        for _, res in pairs(results or {}) do
            showWindow("Phpactor Status", "markdown", res["result"])
        end
    end
