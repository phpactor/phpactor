function! phpactor#quickfix#build(entries) abort
    let shouldUseFzf = v:true == get(g:, 'phpactorUseFzfForQuickfix', v:true)

    " fzf.vim is required for fzf#vim#with_preview()
    if shouldUseFzf && exists("*fzf#complete")
        call phpactor#fzf#quickfix(a:entries)
    else
        call setqflist(values(a:entries))
        cw " Open only if there is recognized errors only
    endif
endfunction

" vim: et ts=4 sw=4 fdm=marker
