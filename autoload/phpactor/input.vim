function! phpactor#input#text(label, default, completionType, ResultHandler)
    if v:null != a:completionType
        let text = input(a:label, a:default, a:completionType)
    else
        let text = input(a:label, a:default)
    endif

    call a:ResultHandler(text)
endfunction

function! phpactor#input#confirm(label, ResultHandler)
    let choice = confirm(a:label, "&Yes\n&No\n")

    if choice == 1
        let response = v:true
    else
        let response = v:false
    endif

    call a:ResultHandler(response)
endfunction

let s:usedShortcuts = []
function! phpactor#input#choice(label, choices, keyMap, ResultHandler)
    let s:usedShortcuts = []
    let list = []

    if empty(a:choices)
        call confirm("No choices available")
        throw "cancelled"
    endif

    for choiceLabel in keys(a:choices)
        let buffer = []

        " note that a:keyMap can be an empty list because PHP's json_decode
        " can't tell the difference between an empty list and an empty dict
        if !empty(a:keyMap) && has_key(a:keyMap, choiceLabel) && !empty(a:keyMap[choiceLabel])
            let confirmLabel = s:determineConfirmLabelFromPreference(choiceLabel, a:keyMap[choiceLabel])
        else
            let confirmLabel = s:determineConfirmLabel(choiceLabel)
        endif

        call add(list, confirmLabel)
    endfor

    let choice = confirm(a:label, join(list, "\n"))

    if (choice == 0)
        " this is an exception, not a message!
        throw "cancelled"
    endif

    call a:ResultHandler(keys(a:choices)[choice - 1])
endfunction

function! s:determineConfirmLabelFromPreference(choiceLabel, preference)
    let buffer = []
    let foundShortcut = v:false

    for char in split(a:choiceLabel, '\zs')
        if foundShortcut == v:false && tolower(char) == tolower(a:preference)
            let foundShortcut = v:true

            call add(buffer, '&' . a:preference)
            call add(s:usedShortcuts, a:preference)
            continue
        endif

        call add(buffer, char)
    endfor

    if foundShortcut == v:false
        " Could not find char in the label - add the shortcut at the end
        call add(buffer, '&' . a:preference)
    endif

    return join(buffer, "")
endfunction

function! s:determineConfirmLabel(choiceLabel)
    let foundShortcut = v:false
    let buffer = []
    for char in split(a:choiceLabel, '\zs')
        if v:false == foundShortcut && -1 == index(s:usedShortcuts, tolower(char))
            let foundShortcut = v:true

            call add(buffer, '&')
            call add(s:usedShortcuts, tolower(char))
        endif

        call add(buffer, char)
    endfor

    return join(buffer, "")
endfunction

function! phpactor#input#list(label, choices, multi, ResultHandler)
    let choices = sort(keys(a:choices))

    try
        let strategy = g:phpactorInputListStrategy
        call call(strategy, [a:label, choices, a:multi, a:ResultHandler])
    catch /E117/
        redraw!
        echo 'The strategy "'. strategy .'" is unknown, check the value of "g:phpactorInputListStrategy".'
    endtry
endfunction

" vim: et ts=4 sw=4 fdm=marker
