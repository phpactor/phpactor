"  ______    __    __  .______      ___       ______ .___________.  ______   .______      
" |   _  \  |  |  |  | |   _  \    /   \     /      ||           | /  __  \  |   _  \     
" |  |_)  | |  |__|  | |  |_)  |  /  ^  \   |  ,----'`---|  |----`|  |  |  | |  |_)  |    
" |   ___/  |   __   | |   ___/  /  /_\  \  |  |         |  |     |  |  |  | |      /     
" |  |      |  |  |  | |  |     /  _____  \ |  `----.    |  |     |  `--'  | |  |\  \----.
" | _|      |__|  |__| | _|    /__/     \__\ \______|    |__|      \______/  | _| `._____|
"                                                                                         

let g:phpactorpath = expand('<sfile>:p:h') . '/..'
let g:phpactorbinpath = g:phpactorpath. '/bin/phpactor'
let g:phpactorPhpBin = 'php'
let g:phpactorInitialCwd = getcwd()
let g:phpactorBranch = 'master'
let g:phpactorOmniError = v:false

"""""""""""""""""
" Update Phpactor
"""""""""""""""""
function! phpactor#Update()
    let current = getcwd()
    execute 'cd ' . g:phpactorpath
    echo system('git checkout ' . g:phpactorBranch)
    echo system('git pull origin ' . g:phpactorBranch)
    echo system('composer install')
    execute 'cd ' .  current
endfunction

""""""""""""""""""""""""
" Autocomplete
""""""""""""""""""""""""
function! phpactor#Complete(findstart, base)

    if a:findstart
        let line = getline('.')
        let start = col('.')
        let originalStart = start
        let triggers = [ "->", "::" ]

        while start -1 >= 0

            if line[start-1:start-1] == "$"
                return start
            endif

            if index(triggers, line[start-2:start-1]) >= 0
                return start
            endif

            let start -= 1
        endwhile

        return start
    endif

    let offset = line2byte(line(".")) + col('.') - 2
    let source = join(getline(1,'.'), "\n")
    let source = source . a:base
    let offset = offset + strlen(a:base)
    let source = source . "\n" . join(getline(line('.') + 1, '$'), "\n")

    let result = phpactor#rpc("complete", { "offset": offset, "source": source})
    let suggestions = result['suggestions']
    let issues = result['issues']

    let completions = []
    if !empty(suggestions)
        for suggestion in suggestions
            call add(completions, { 'word': suggestion['name'], 'menu': suggestion['info'], 'kind': suggestion['type']})
        endfor
    endif

    if !empty(issues)
        if g:phpactorOmniError
            echoe join(issues, ', ')
        endif
    endif

    return completions
endfunc

""""""""""""""""""""""""
" Extract method
""""""""""""""""""""""""
function! phpactor#ExtractMethod()
    let selectionStart = phpactor#_selectionStart()
    let selectionEnd = phpactor#_selectionEnd()
    let currentPath = expand('%')

    call phpactor#rpc("extract_method", { "path": currentPath, "offset_start": selectionStart, "offset_end": selectionEnd, "source": phpactor#_source()})
endfunction

function! phpactor#ClassExpand()
    let word = expand("<cword>")
    let classInfo = phpactor#rpc("class_search", { "short_name": word })

    if (empty(classInfo))
        return
    endif

    let line = getline('.')
    let char = line[col('.') - 2]
    let namespace_prefix = classInfo['class_namespace'] . "\\"

    " If this is the start of the word
    if (col('.') == 1 || ' ' == char || '(' == char)
        execute "normal! i" . namespace_prefix
        return
    endif

    " otherwise goto start of word
    execute "normal! bi" . namespace_prefix
endfunction

""""""""""""""""""""""""
" Insert a use statement
""""""""""""""""""""""""
function! phpactor#UseAdd()
    let word = expand("<cword>")
    call phpactor#rpc("import_class", {"name": word, "offset": phpactor#_offset(), "source": phpactor#_source(), "path": expand('%:p')})
endfunction

"""""""""""""""""""""""""""
" RPC Proxy methods
"""""""""""""""""""""""""""
function! phpactor#GotoDefinition()
    call phpactor#rpc("goto_definition", { "offset": phpactor#_offset(), "source": phpactor#_source(), "path": expand('%:p')})
endfunction

function! phpactor#ContextMenu()
    call phpactor#rpc("context_menu", { "offset": phpactor#_offset(), "source": phpactor#_source(), "current_path": expand('%:p') })
endfunction

function! phpactor#CopyFile()
    let currentPath = expand('%')
    call phpactor#rpc("copy_class", { "source_path": currentPath })
endfunction

function! phpactor#MoveFile()
    let currentPath = expand('%')
    call phpactor#rpc("move_class", { "source_path": currentPath })
endfunction

function! phpactor#OffsetTypeInfo()
    call phpactor#rpc("offset_info", { "offset": phpactor#_offset(), "source": phpactor#_source()})
endfunction

function! phpactor#Transform()
    let currentPath = expand('%')
    call phpactor#rpc("transform", { "path": currentPath, "source": phpactor#_source() })
endfunction

function! phpactor#ClassNew()
    let currentPath = expand('%')
    call phpactor#rpc("class_new", { "current_path": currentPath })
endfunction

function! phpactor#ClassInflect()
    let currentPath = expand('%')
    call phpactor#rpc("class_inflect", { "current_path": currentPath })
endfunction

" Deprecated!! Use FindReferences
function! phpactor#ClassReferences()
    call phpactor#FindReferences()
endfunction

function! phpactor#FindReferences()
    call phpactor#rpc("references", { "offset": phpactor#_offset(), "source": phpactor#_source()})
endfunction

function! phpactor#Navigate()
    let currentPath = expand('%')
    call phpactor#rpc("navigate", { "source_path": currentPath })
endfunction

function! phpactor#CacheClear()
    call phpactor#rpc("cache_clear", {})
endfunction

function! phpactor#Status()
    call phpactor#rpc("status", {})
endfunction

function! phpactor#Config()
    call phpactor#rpc("config", {})
endfunction

"""""""""""""""""""""""
" Utility functions
"""""""""""""""""""""""
function! phpactor#_switchToBufferOrEdit(filePath)
    let bufferNumber = bufnr(a:filePath . '$')

    if (bufferNumber == -1)
        exec ":edit " . a:filePath
        return
    endif

    exec ":buffer " . bufferNumber
endfunction

function! phpactor#_offset()
    return line2byte(line('.')) + col('.') - 1
endfunction

function! phpactor#_source()
    return join(getline(1,'$'), "\n")
endfunction

function! phpactor#_selectionStart()
    let [lineStart, columnStart] = getpos("'<")[1:2]
    return line2byte(lineStart) + columnStart -2
endfunction

function! phpactor#_selectionEnd()
    let [lineEnd, columnEnd] = getpos("'>")[1:2]

    " Note VIM returns 2,147,483,647 on this system when in block select mode
    if (columnEnd > 1000000)
        let columnEnd = strlen(getline(lineEnd))
    endif

    return line2byte(lineEnd) + columnEnd -1
endfunction


"""""""""""""""""""""""
" RPC -->-->-->-->-->--
"""""""""""""""""""""""

function! phpactor#rpc(action, arguments)
    " Remove any existing output in the message window
    execute ':redraw'

    let request = { "action": a:action, "parameters": a:arguments }

    let cmd = g:phpactorPhpBin . ' ' . g:phpactorbinpath . ' rpc --working-dir=' . g:phpactorInitialCwd
    let result = system(cmd, json_encode(request))

    if (v:shell_error == 0)
        let response = json_decode(result)

        let actionName = response['action']
        let parameters = response['parameters']

        let response = phpactor#_rpc_dispatch(actionName, parameters)

        if !empty(response)
            return response
        endif
    else
        echo "Phpactor returned an error: " . result
        return
    endif
endfunction

function! phpactor#_input_choice(label, choices)
    let list = []
    let choices = []
    let usedShortcuts = []

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

    let choice = choice - 1
    return a:choices[get(choices, choice)]
endfunction

function! phpactor#_input_list(label, choices)
    let list = []
    let choices = []

    let c = 1
    for choiceLabel in keys(a:choices)
        call add(list, c . ") " . choiceLabel)
        call add(choices, choiceLabel)
        let c = c + 1
    endfor

    echo a:label
    let choice = inputlist(list)

    if (choice == 0)
        throw "cancelled"
    endif

    let choice = choice - 1
    return a:choices[choices[choice]]
endfunction

function! phpactor#_rpc_dispatch(actionName, parameters)

    " >> return_choice
    if a:actionName == "return"
        return a:parameters["value"]
    endif

    " >> return_choice
    if a:actionName == "return_choice"
        let list = []
        let c = 1
        for choice in a:parameters["choices"]
            call add(list, c . ") " . choice["name"])
            let c = c + 1
        endfor

        let choice = inputlist(list)

        if (choice == 0)
            return
        endif

        let choice = choice - 1

        return a:parameters["choices"][choice]["value"]
    endif

    " >> echo
    if a:actionName == "echo"
        echo a:parameters["message"]
        return
    endif

    " >> error
    if a:actionName == "error"
        echo "Error from Phpactor: " . a:parameters["message"]
        return
    endif

    " >> collection
    if a:actionName == "collection"
        for action in a:parameters["actions"]
            let result = phpactor#_rpc_dispatch(action["name"], action["parameters"])

            if !empty(result)
                return result
            endif
        endfor

        return
    endif

    " >> open_file
    if a:actionName == "open_file"
        call phpactor#_switchToBufferOrEdit(a:parameters['path'])
        exec ":edit"

        if (a:parameters['offset'])
            exec ":goto " .  (a:parameters['offset'] + 1)
            normal! zz
        endif
        return
    endif

    " >> close_file
    if a:actionName == "close_file"
        let bufferNumber = bufnr(a:parameters['path']. '$')

        if (bufferNumber == -1)
            return
        endif

        exec ":bdelete " . bufferNumber
        return
    endif

    " >> file references
    if a:actionName == "file_references"
        let list = []

        for fileReferences in a:parameters['file_references']
            for reference in fileReferences['references']
                call add(list, { 'filename': fileReferences['file'], 'lnum': reference['line_no'], 'col': reference['col_no'] + 1})
            endfor
        endfor

        call setqflist(list)
        execute ':cwindow'
        return
    endif

    " >> input_callback
    if a:actionName == "input_callback"
        let parameters = a:parameters['callback']['parameters']
        for input in a:parameters['inputs']

            try 
                let value = phpactor#_rpc_dispatch_input(input['type'], input['parameters'])
            catch /cancelled/
                execute ':redraw'
                echo "Cancelled"
                return
            endtry

            let parameters[input['name']] = value
        endfor
        call phpactor#rpc(a:parameters['callback']['action'], parameters)
        return
    endif

    " >> information
    if a:actionName == "information"
        " We write to a temporary file and then "edit" it in the preview
        " window. Not sure if there is a better way to do this.
        let temp = resolve(tempname())
        execute 'pedit ' . temp
        wincmd P
        call append(0, split(a:parameters['information'], "\n"))
        execute ":1"
        silent write!
        wincmd p
        return
    endif

    " >> replace_file_source
    if a:actionName == "replace_file_source"
        call phpactor#_switchToBufferOrEdit(a:parameters['path'])

        " save the cursor position
        let savePos = getpos(".")

        " delete everything into the blackhole buffer
        exec "%d _"

        " insert the transformed source code
        execute ":put =a:parameters['source']"

        " `put` will leave a blank line at the start of the file, remove it
        execute ":1delete _"

        " restore the cursor position
        call setpos('.', savePos)
        return
    endif

    throw "Do not know how to handle action '" . a:actionName . "'"
endfunction

function! phpactor#_rpc_dispatch_input(type, parameters)
    " Remove any existing output in the message window
    execute ':redraw'

    " >> text
    if a:type == 'text'
        return input(a:parameters['label'], a:parameters['default'], a:parameters['type'])
    endif

    " >> choice
    if a:type == 'choice'
        return phpactor#_input_choice(a:parameters['label'], a:parameters['choices'])
    endif

    if a:type == 'list'
        return phpactor#_input_list(a:parameters['label'], a:parameters['choices'])
    endif

    " >> confirm
    if a:type == 'confirm'
        let choice = confirm(a:parameters["label"], "&Yes\n&No\n")

        if choice == 1
            return v:true
        endif

        return v:false
    endif


    throw "Do not know how to handle input '" . a:type . "'"
endfunction
