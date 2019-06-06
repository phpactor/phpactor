function! phpactor#input#list#strategy()
    if has_key(g:, 'phpactorInputListStrategy')
        return g:phpactorInputListStrategy
    endif

    if has_key(g:, 'phpactorCustomInputListStrategy')
        let g:phpactorInputListStrategy = g:phpactorCustomInputListStrategy
    else
        let g:phpactorInputListStrategy = s:auto_detect_strategy()
    endif

    return g:phpactorInputListStrategy
endfunction

function! phpactor#input#list#inputlist(label, choices, ResultHandler)
    echo a:label
    let choice = inputlist(s:add_number_to_choices(a:choices))

    if (choice == 0)
        throw "cancelled"
    endif

    call a:ResultHandler(a:choices[choice - 1])
endfunction

function! phpactor#input#list#fzf(label, choices, ResultHandler)
    " sink works because "key" is converted to integer, so only the number is kept
    call fzf#run({
        \ 'source': s:add_number_to_choices(a:choices),
        \ 'sink': {key -> a:ResultHandler(a:choices[key - 1])},
        \ 'down': '30%',
        \ 'options': ['--tiebreak=index', '--layout=reverse-list']
    \ })
endfunction

function! s:auto_detect_strategy()
    let strategy = 'inputlist'

    if get(g:, 'loaded_fzf', 0)
        let strategy = 'fzf'
    endif

    return 'phpactor#input#list#'. strategy
endfunction

function! s:add_number_to_choices(choices)
    return map(copy(a:choices), {key, value -> key + 1 .') '. value})
endfunction

" vim: et ts=4 sw=4 fdm=marker
