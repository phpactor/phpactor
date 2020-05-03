augroup PhpactorInit
    autocmd! * <buffer>
    autocmd CompleteDone <buffer> call phpactor#_completeImportClass(v:completed_item)
augroup END


" vim: et ts=4 sw=4 fdm=marker
