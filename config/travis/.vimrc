filetype off
let s:phpactorRootDir = expand('<sfile>:p:h:h:h')
let &runtimepath .= ',' . expand(s:phpactorRootDir . '/vader.vim')
let &runtimepath .= ',' . s:phpactorRootDir
let &runtimepath .= ',' . expand(s:phpactorRootDir . '/after')
filetype plugin indent on
syntax enable
