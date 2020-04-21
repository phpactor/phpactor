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
" The list of directories that should not be considered as workspace root directory
" (in addition to '/' which is always considered)
let g:phpactorGlobalRootPatterns = get(g:, 'phpactorGlobalRootPatterns', ['/', '/home'])

" vim: et ts=4 sw=4 fdm=marker
