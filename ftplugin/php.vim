"  ______    __    __  .______      ___       ______ .___________.  ______   .______
" |   _  \  |  |  |  | |   _  \    /   \     /      ||           | /  __  \  |   _  \
" |  |_)  | |  |__|  | |  |_)  |  /  ^  \   |  ,----'`---|  |----`|  |  |  | |  |_)  |
" |   ___/  |   __   | |   ___/  /  /_\  \  |  |         |  |     |  |  |  | |      /
" |  |      |  |  |  | |  |     /  _____  \ |  `----.    |  |     |  `--'  | |  |\  \----.
" | _|      |__|  |__| | _|    /__/     \__\ \______|    |__|      \______/  | _| `._____|
"

if exists('g:phpactorLoaded')
  finish
endif

let g:phpactorLoaded = 1
let g:phpactorpath = expand('<sfile>:p:h') . '/..'
let g:phpactorbinpath = g:phpactorpath. '/bin/phpactor'
let g:phpactorInitialCwd = getcwd()
let g:phpactorCompleteLabelTruncateLength=50
let g:_phpactorCompletionMeta = {}

if !exists('g:phpactorPhpBin')
    ""
    " Path to the PHP binary used by Phpactor
    let g:phpactorPhpBin = 'php'
endif

if !exists('g:phpactorBranch')
    ""
    " The Phpactor branch to use when calling @command(PhpactorUpdate)
    let g:phpactorBranch = 'master'
endif

if !exists('g:phpactorOmniAutoClassImport')
    ""
    " Automatically import classes when using VIM native omni-completion
    let g:phpactorOmniAutoClassImport = v:true
endif

if !exists('g:phpactorCompletionIgnoreCase')
    ""
    " Ignore case when suggestion completion results
    let g:phpactorCompletionIgnoreCase = 1
endif

if !exists('g:phpactorQuickfixStrategy')
    ""
    " Function to use when populating a list of code references. The default
    " is to use the VIM quick-fix list.
    let g:phpactorQuickfixStrategy = 'phpactor#quickfix#vim'
endif

if !exists('g:phpactorInputListStrategy')
    ""
    " Function to use when presenting a user with a choice of options. The default
    " is to use the VIM inputlist.
    let g:phpactorInputListStrategy = 'phpactor#input#list#inputlist'
endif

if g:phpactorOmniAutoClassImport == v:true
    autocmd CompleteDone *.php call phpactor#_completeImportClass(v:completed_item)
endif


" vim: et ts=4 sw=4 fdm=marker

