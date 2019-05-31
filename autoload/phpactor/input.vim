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

function! phpactor#input#choice(label, choices, ResultHandler)
    let list = []
    let choices = []
    let usedShortcuts = []

    if empty(a:choices)
        call confirm("No choices available")
        throw "cancelled"
    endif

    for choiceLabel in keys(a:choices)
        let buffer = []
        let foundShortcut = v:false

        for char in split(choiceLabel, '\zs')
            if v:false == foundShortcut && -1 == index(usedShortcuts, tolower(char))
                call add(buffer, '&')
                let foundShortcut = v:true
                call add(usedShortcuts, tolower(char))
            endif

            call add(buffer, char)
        endfor

        let confirmLabel = join(buffer, "")

        call add(list, confirmLabel)
        call add(choices, choiceLabel)
    endfor

    let choice = confirm(a:label, join(list, "\n"))

    if (choice == 0)
        " this is an exception, not a message!
        throw "cancelled"
    endif

    call a:ResultHandler(choices[choice - 1])
endfunction

function! phpactor#input#list(label, choices, ResultHandler)
    let choices = sort(keys(a:choices))

    try
      let strategy = phpactor#input#list#strategy()
      call call(strategy, [a:label, choices, a:ResultHandler])
    catch /E117/
      redraw!
      echo 'The strategy "'. strategy .'" is unknown, check the value of "g:phpactorInputListStrategy".'
    endtry
endfunction
