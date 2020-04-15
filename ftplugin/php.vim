function! s:import_on_complete_done(completed_item) abort
    if get(b:, 'phpactorOmniAutoClassImport', g:phpactorOmniAutoClassImport) != v:true
      return
    endif

    call phpactor#_completeImportClass(a:completed_item)
endfunction

augroup PhpactorInit
    autocmd! * <buffer>
    autocmd CompleteDone <buffer> call <SID>import_on_complete_done(v:completed_item)
augroup END


" vim: et ts=4 sw=4 fdm=marker
