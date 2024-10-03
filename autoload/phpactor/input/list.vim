function! phpactor#input#list#inputlist(label, choices, multi, ResultHandler)
    echo a:label
    let choice = inputlist(s:add_number_to_choices(a:choices))

    if (choice == 0)
        throw "cancelled"
    endif

    call a:ResultHandler(a:choices[choice - 1])
endfunction

" expreimental: this strategy currently does not work when used in a 
" non-terminal RPC step - https://github.com/phpactor/phpactor/issues/845
function! phpactor#input#list#fzf(label, choices, multi, ResultHandler)
    let options = [
        \ '--tiebreak=index',
        \ '--layout=reverse-list',
    \ ]
    let sink = {
        \ 'sink': {key -> a:ResultHandler(a:choices[key - 1])},
    \ }

    if a:multi
        call extend(options, [
            \ '--multi',
            \ '--bind=ctrl-a:select-all,ctrl-d:deselect-all',
        \ ])

        let sink = {
            \ 'sink*': {results -> a:ResultHandler(map(
                \ results,
                \ {key, value -> a:choices[value - 1]}
            \ ))}
        \ }
    endif

    " sink works because "key" is converted to integer, so only the number is kept
    call fzf#run(extend({
        \ 'source': s:add_number_to_choices(a:choices),
        \ 'down': '30%',
        \ 'options': options
    \ }, sink))
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
