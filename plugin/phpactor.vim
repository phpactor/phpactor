if exists('g:loaded_phpactor')
  finish
endif

let g:loaded_phpactor = 1
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
let g:phpactorProjectRootPatterns = get(g:, 'phpactorProjectRootPatterns', ['composer.json', '.git', '.phpactor.json', '.phpactor.yml'])

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
let g:phpactorNoninteractiveProjectResolvers = get(g:, 'phpactorProjectResolvers', ['rootMarkers'])

""
" Once a php buffer has been open prompt user how to resolve its project root.
" Note that  you can have to `set shortmess -=F`
" otherwise it probably will not work.
let g:phpactorAllowInteractiveProjectResolution = get(g:, 'phpactorAllowInteractiveProjectResolution', v:false)

" vim: et ts=4 sw=4 fdm=marker
