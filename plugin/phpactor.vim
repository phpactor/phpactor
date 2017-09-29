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

"""""""""""""""""
" Update Phpactor
"""""""""""""""""
function! phpactor#Update()
    let current = getcwd()
    execute 'cd ' . g:phpactorpath
    echo system('git pull origin master')
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
        let triggers = [ "->", "::" ]

        while start -2 > 0 && -1 == index(triggers, line[start-2:start-1])
            let start -= 1
        endwhile

        return start
    endif

    let offset = line2byte(line(".")) + col('.') - 2
    let source = join(getline(1,'.'), "\n")
    let source = source . "\n" . join(getline(line('.') + 1, '$'), "\n")

    let suggestions = phpactor#rpc("complete", { "offset": offset, "source": source})

    let completions = []
    if !empty(suggestions)
        for suggestion in suggestions
            call add(completions, { 'word': suggestion['name'], 'menu': suggestion['info'], 'kind': suggestion['type']})
        endfor
    endif

    return completions
endfunc

""""""""""""""""""""""""
" Expand a use statement
""""""""""""""""""""""""
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

    ""
    " @return int Number of extra lines added
    ""
    function! UseAdd(savePos)
        let word = expand("<cword>")
        let classInfo = phpactor#rpc("class_search", { "short_name": word })

        if (empty(classInfo))
            return
        endif

        call cursor(1, 1)
        let existing = search('^.*use.*\\' . classInfo['class_name'] . ';$')

        if (existing > 0)
            echo "Use statement already included on line:" . existing
            call setpos('.', a:savePos)
            return 0
        endif

        " START: Insert use statement
        call cursor(1, 1)
        let namespaceLineNb = search('^namespace') + 1

        " Find an appropriate place to put the use statement,
        " if there is no namespace, put it after the start tag
        if (namespaceLineNb == 0)
            let namespaceLineNb = 2
        endif

        " Search for the last use statement
        call cursor(1, 1)
        let lastUseLineNb = namespaceLineNb
        let result = -1
        while (result != 0)
            let result = search('^use', '', line("w$"))

            if (result > 0)
                let lastUseLineNb = result
            endif
        endwhile

        " Try and put the cursor at the best place
        call cursor(lastUseLineNb, 1)

        " Ensure an empty line before the use statement
        let extraLines = 1
        let line = getline(line('.') + 1)
        if (!empty(line))
            exec "normal! O"
            let extraLines += 1
        endif

        " Insert use statement
        execute "normal! ouse " . classInfo['class'] . ";"

        " Ensure an empty line afterwards
        let line = getline(line('.') + 1)
        if (!empty(line))
            exec "normal! o"
            let extraLines += 1
        endif

        return extraLines
    endfunc

    let savePos = getpos(".")
    let extraLines = UseAdd(savePos)

    if extraLines
        let savePos = [savePos[0], savePos[1] + extraLines, savePos[2], savePos[3]]
    endif

    call setpos('.', savePos)
endfunction

"""""""""""""""""""""""""""
" RPC Proxy methods
"""""""""""""""""""""""""""
function! phpactor#GotoDefinition()
    call phpactor#rpc("goto_definition", { "offset": phpactor#_offset(), "source": phpactor#_source()})
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
    exec ":edit"
endfunction

function! phpactor#_offset()
    return line2byte(line('.')) + col('.') - 1
endfunction

function! phpactor#_source()
    return join(getline(1,'$'), "\n")
endfunction


"""""""""""""""""""""""
" RPC -->-->-->-->-->--
"""""""""""""""""""""""

function! phpactor#rpc(action, arguments)
    " Remove any existing output in the message window
    execute ':redraw'

    let request = {"actions": [ { "action": a:action, "parameters": a:arguments } ] }

    let cmd = g:phpactorPhpBin . ' ' . g:phpactorbinpath . ' rpc --working-dir=' . g:phpactorInitialCwd
    let result = system(cmd, json_encode(request))

    if (v:shell_error == 0)
        let result = json_decode(result)

        for action in result['actions']
            let actionName = action['action']
            let parameters = action['parameters']

            let result = phpactor#_rpc_dispatch(actionName, parameters)

            if !empty(result)
                return result
            endif
        endfor
    else
        echo "Phpactor returned an error: " . result
        return
    endif
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
        let savePos = getpos(".")
        exec "%d"
        call append(0, split(a:parameters['source'], "\n"))
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

        let list = []
        let choices = []

        let c = 1
        for choiceLabel in keys(a:parameters["choices"])
            call add(list, c . ") " . choiceLabel)
            call add(choices, choiceLabel)
            let c = c + 1
        endfor

        echo a:parameters['label']
        let choice = inputlist(list)

        if (choice == 0)
            throw "cancelled"
        endif

        let choice = choice - 1
        return a:parameters['choices'][choices[choice]]
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
