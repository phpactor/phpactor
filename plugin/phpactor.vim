if exists('g:loaded_phpactor')
  finish
endif

let g:loaded_phpactor = 1

" Config {{{
let g:phpactorpath = expand('<sfile>:p:h') . '/..'
let g:phpactorbinpath = g:phpactorpath. '/bin/phpactor'
let g:phpactorInitialCwd = getcwd()
let g:phpactorCompleteLabelTruncateLength=50

""
" Path to the PHP binary used by Phpactor
let g:phpactorPhpBin = get(g:, 'phpactorPhpBin', 'php')

""
" The Phpactor branch to use when calling @command(PhpactorUpdate)
let g:phpactorBranch = get(g:, 'phpactorBranch', 'master')

""
" Automatically import classes when using VIM native omni-completion
let g:phpactorOmniAutoClassImport = get(g:, 'phpactorOmniAutoClassImport', v:true)

""
" Ignore case when suggestion completion results
let g:phpactorCompletionIgnoreCase = get(g:, 'phpactorCompletionIgnoreCase', 1)

""
" Function to use when populating a list of code references. The default
" is to use the VIM quick-fix list.
let g:phpactorQuickfixStrategy = get(g:, 'phpactorQuickfixStrategy', 'phpactor#quickfix#vim')

""
" Function to use when presenting a user with a choice of options. The default
" is to use the VIM inputlist.
let g:phpactorInputListStrategy = get(g:, 'phpactorInputListStrategy', 'phpactor#input#list#inputlist')

""
" When jumping to a file location: if the target file open in a window, switch
" to that window instead of switching buffers.  The default is false.
let g:phpactorUseOpenWindows = get(g:, 'phpactorUseOpenWindows', v:false)

""
" Each Phpactor request requires the project's root directory to be known. By
" default it will assume the directory in which you started VIM, but this may
" not suit all workflows.
"
" This setting allows |Funcref| to be specified. This function should return
" the working directory in whichever way is required. No arguments are passed
" to this function.
let g:PhpactorRootDirectoryStrategy = get(g:, 'PhpactorRootDirectoryStrategy', {-> g:phpactorInitialCwd})

" Config }}}

" Commands {{{

""
" Update Phpactor to the latest version using the branch
" defined with @setting(g:phpactorBranch)
command! -nargs=0 PhpactorUpdate call phpactor#Update()

""
" Clear the entire cache - this will take effect for all projects.
command! -nargs=0 PhpactorCacheClear call phpactor#CacheClear()

""
" Show some information about Phpactor's status
command! -nargs=0 PhpactorStatus call phpactor#Status()

""
" Dump Phpactor's configuration
command! -nargs=0 PhpactorConfig call phpactor#Config()

""
" Expand the class name under the cursor to it's fully-qualified-name
command! -nargs=0 PhpactorClassExpand call phpactor#ClassExpand()

""
" Create a new class. You will be offered a choice of templates.
command! -nargs=0 PhpactorClassNew call phpactor#ClassNew()

""
" @default target=`edit`
"
" Goto the definition of the symbol under the cursor.
" Opens in the [target] window, see @section(window-target) for
" the list of possible targets.
" |<mods>| can be provided to the command to change how the window will be
" opened.
"
" Examples:
" >
"     " Opens in the current buffer
"     PhpactorGotoDefinition
"
"     " Opens in a vertical split opened on the right side
"     botright PhpactorGotoDefinition vsplit
"     vertical botright PhpactorGotoDefinition split
"
"     " Opens in a new tab
"     PhpactorGotoDefinition tabnew
" <
command! -nargs=? -complete=customlist,s:CompleteWindowTarget PhpactorGotoDefinition call phpactor#GotoDefinition(<q-args>, <q-mods>)
""
" deprecated, use @command(PhpactorGotoDefinition) instead
"
" As with @command(PhpactorGotoDefinition) but open in a vertical split.
command! -nargs=0 PhpactorGotoDefinitionVsplit
  \ echoerr 'PhpactorGotoDefinitionVsplit is deprecated, use PhpactorGotoDefinition instead' |
  \ <mods> PhpactorGotoDefinition vsplit
""
" deprecated, use @command(PhpactorGotoDefinition) instead
"
" As with @command(PhpactorGotoDefinition) but open in an horizontal split.
command! -nargs=0 PhpactorGotoDefinitionHsplit
  \ echoerr 'PhpactorGotoDefinitionHsplit is deprecated, use PhpactorGotoDefinition instead' |
  \ <mods> PhpactorGotoDefinition hsplit
""
" deprecated, use @command(PhpactorGotoDefinition) instead
"
" As with @command(PhpactorGotoDefinition) but open in a new tab.
command! -nargs=0 PhpactorGotoDefinitionTab
  \ echoerr 'PhpactorGotoDefinitionTab is deprecated, use PhpactorGotoDefinition instead' |
  \ PhpactorGotoDefinition new_tab

""
" @usage [target]
"
" Same as @command(PhpactorGotoDefinition) but goto the type of the symbol
" under the cursor.
command! -nargs=? -complete=customlist,s:CompleteWindowTarget PhpactorGotoType call phpactor#GotoType(<q-args>, <q-mods>)

""
" @usage [target]
"
" Same as @command(PhpactorGotoDefinition) but goto the implementation of the
" symbol under the cursor.
"
" If there is more than one result the quickfix strategy will be used and [target]
" will be ignored, see @setting(g:phpactorQuickfixStrategy).
command! -nargs=? -complete=customlist,s:CompleteWindowTarget PhpactorGotoImplementations call phpactor#GotoImplementations(<q-args>, <q-mods>)

" Commands }}}

" Functions {{{

function! s:CompleteWindowTarget(argLead, ...) abort
    return filter(phpactor#windowTargets(), {k,v -> 0 == stridx(v, a:argLead)})
endfunction

" }}}

" vim: et ts=4 sw=4 fdm=marker
