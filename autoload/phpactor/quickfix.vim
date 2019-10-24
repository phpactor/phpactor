function! phpactor#quickfix#build(entries) abort
    if exists("*fzf#complete") " If fzf.vim is installed, required for with_preview !
        call phpactor#fzf#quickfix(a:entries)
    else
        call setqflist(values(a:entries))
        cw " Open only if there is recognized errors only
    endif
endfunction

" vim: et ts=4 sw=4 fdm=marker
