if exists('g:phpactorLoaded')
  finish
endif

let g:phpactorLoaded = 1
let g:phpactorpath = expand('<sfile>:p:h') . '/..'
let g:phpactorbinpath = g:phpactorpath. '/bin/phpactor'
let g:phpactorInitialCwd = getcwd()
let g:phpactorCompleteLabelTruncateLength=50
let g:_phpactorCompletionMeta = {}

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

if g:phpactorOmniAutoClassImport == v:true
    autocmd CompleteDone *.php call phpactor#_completeImportClass(v:completed_item)
endif


" vim: et ts=4 sw=4 fdm=marker
