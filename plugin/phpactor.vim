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
" List all installed extensions
command! -nargs=0 PhpactorExtensionList call phpactor#ExtensionList()

""
" Install an extension
command! -nargs=1 PhpactorExtensionInstall call phpactor#ExtensionInstall(<q-args>)

""
" Remove an extension
command! -nargs=1 PhpactorExtensionRemove call phpactor#ExtensionRemove(<q-args>)

""
" Expand the class name under the cursor to it's fully-qualified-name
command! -nargs=0 PhpactorClassExpand call phpactor#ClassExpand()

""
" Create a new class. You will be offered a choice of templates.
command! -nargs=0 PhpactorClassNew call phpactor#ClassNew()

""
" @default target=`focused_window`
"
" Goto the definition of the symbol under the cursor.
" Opens in the [target] window, see @section(window-target) for
" the list of possible targets.
command! -nargs=? -complete=customlist,s:CompleteWindowTarget PhpactorGotoDefinition call phpactor#GotoDefinition(<f-args>)
""
" deprecated, use @command(PhpactorGotoDefinition) instead
"
" As with @command(PhpactorGotoDefinition) but open in a vertical split.
command! -nargs=0 PhpactorGotoDefinitionVsplit 
  \ echoerr 'PhpactorGotoDefinitionVsplit is deprecated, use PhpactorGotoDefinition instead' |
  \ PhpactorGotoDefinition vsplit
""
" deprecated, use @command(PhpactorGotoDefinition) instead
"
" As with @command(PhpactorGotoDefinition) but open in an horizontal split.
command! -nargs=0 PhpactorGotoDefinitionHsplit 
  \ echoerr 'PhpactorGotoDefinitionHsplit is deprecated, use PhpactorGotoDefinition instead' |
  \ PhpactorGotoDefinition hsplit
""
" deprecated, use @command(PhpactorGotoDefinition) instead
"
" As with @command(PhpactorGotoDefinition) but open in a new tab.
command! -nargs=0 PhpactorGotoDefinitionTab 
  \ echoerr 'PhpactorGotoDefinitionTab is deprecated, use PhpactorGotoDefinition instead' |
  \ PhpactorGotoDefinition new_tab

""
" Goto type (class) of the symbol under the cursor.
command! -nargs=0 PhpactorGotoType call phpactor#GotoType()

""
" Load all implementations of the class under the cursor into the quick-fix
" list.
command! -nargs=0 PhpactorGotoImplementations call phpactor#GotoImplementations()

" Commands }}}

" Functions {{{

""
" @section Window targets, window-targets
" @parentsection commands
"
" Phpactor provide a few window targets to use with some commands and
" functions.
" See @command(PhpactorGotoDefinition) or @function(phpactor#GotoDefinition)
" for an example of how to use them.
"
" Possible values are:
" * `focused_window`: open in the current window or in the window containing the
"   destination buffer if it exists in the current tab
" * `hsplit`: open in an horizontal split window
" * `vplist`: open in a vertical split window
" * `new_tab`: open in a new tab

function! s:CompleteWindowTarget(...) abort
  return ['focused_window', 'vsplit', 'hsplit', 'new_tab']
endfunction

" }}}

" vim: et ts=4 sw=4 fdm=marker
