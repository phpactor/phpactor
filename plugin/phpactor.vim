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
" The list of files that determine workspace root directory
" if contained within
let g:phpactorProjectRootPatterns = get(g:, 'phpactorProjectRootPatterns', [])

""
" The list of directories that should not be considered as workspace root directory when resolve project root by root pattern
" (in addition to '/' which is always considered)
let g:phpactorGlobalRootPatterns = get(g:, 'phpactorGlobalRootPatterns', ['/', '/home'])
""
" The list of enabled methods used to resolve the project root.
" The order matters: if a given method succeeded, the rest are ignored.
" Valid are 'initialCwd', 'rootMarkers', 'manual'. Invalid entries will be ignored.
" 'initialCwd' always returns with a success and will be always appended to this list as a fallback.
" There is no need to add 'initialCwd' explicitely because any method present after it will be ignored.
" Since the resolver can be run from autocommand, you can have to `set shortmess -=F`
" to prevent disable user prompt needed to use 'manual'.
let g:phpactorNoninteractiveProjectResolvers = get(g:, 'phpactorNoninteractiveProjectResolvers', ['rootMarkers'])

""
" Once a php buffer has been open prompt user how to resolve its project root.
" Note that  you can have to `set shortmess -=F`
" otherwise it probably will not work.
let g:phpactorAllowInteractiveProjectResolution = get(g:, 'phpactorAllowInteractiveProjectResolution', v:false)

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
" Goto the definition of the class, method or function under the cursor. Open
" the definition in the current window.
command! -nargs=0 PhpactorGotoDefinition call phpactor#GotoDefinition()

""
" As with @command(PhpactorGotoDefinition) but open in a vertical split.
command! -nargs=0 PhpactorGotoDefinitionVsplit call phpactor#GotoDefinitionVsplit()

""
" As with @command(PhpactorGotoDefinition) but open in a horizontal split.
command! -nargs=0 PhpactorGotoDefinitionHsplit call phpactor#GotoDefinitionHsplit()

""
" As with @command(PhpactorGotoDefinition) but open in a new tab
command! -nargs=0 PhpactorGotoDefinitionTab call phpactor#GotoDefinitionTab()

""
" Goto type (class) of the symbol under the cursor.
command! -nargs=0 PhpactorGotoType call phpactor#GotoType()

""
" Load all implementations of the class under the cursor into the quick-fix
" list.
command! -nargs=0 PhpactorGotoImplementations call phpactor#GotoImplementations()

" Commands }}}

" vim: et ts=4 sw=4 fdm=marker
