augroup PhpactorInit
    autocmd! * <buffer>
    autocmd CompleteDone <buffer> call phpactor#_completeImportClass(v:completed_item)
augroup END

let g:phpactorProjectAssigner = get(g:, 'phpactorProjectAssigner', phpactor#project#assigner#create(
            \ phpactor#project#repository#create(),
            \ g:phpactorProjectRootPatterns,
            \ g:phpactorGlobalRootPatterns,
            \ g:phpactorInitialCwd
            \ ))

" vim: et ts=4 sw=4 fdm=marker
