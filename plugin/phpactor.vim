"  ______    __    __  .______      ___       ______ .___________.  ______   .______      
" |   _  \  |  |  |  | |   _  \    /   \     /      ||           | /  __  \  |   _  \     
" |  |_)  | |  |__|  | |  |_)  |  /  ^  \   |  ,----'`---|  |----`|  |  |  | |  |_)  |    
" |   ___/  |   __   | |   ___/  /  /_\  \  |  |         |  |     |  |  |  | |      /     
" |  |      |  |  |  | |  |     /  _____  \ |  `----.    |  |     |  `--'  | |  |\  \----.
" | _|      |__|  |__| | _|    /__/     \__\ \______|    |__|      \______/  | _| `._____|
"                                                                                         

let s:phpactorpath = expand('<sfile>:p:h') . '/..'
let s:phpactorbinpath = s:phpactorpath. '/bin/phpactor'
let s:phpactorInitialCwd = getcwd()

function! phpactor#NamespaceGet()
    let currentPath = expand('%')
    let command = 'file:info --format=json ' . currentPath
    let out = phpactor#Exec(command)
    let results = json_decode(out)

    return results['class_namespace']
endfunction

"""""""""""""""""
" Update Phpactor
"""""""""""""""""
function! phpactor#Update()
    let current = getcwd()
    execute 'cd ' . s:phpactorpath
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
" Goto definition
"""""""""""""""""""""""""""
function! phpactor#GotoDefinition()
    call phpactor#rpc("goto_definition", { "offset": phpactor#_offset(), "source": phpactor#_source()})
endfunction

"""""""""""""""""""""""""""
" Interactively copy a file
"""""""""""""""""""""""""""
function! phpactor#CopyFile()
    let currentPath = expand('%')
    let destPath = input("Copy to: ", currentPath, "file")
    let command = 'class:copy ' . currentPath . ' ' . destPath
    let out = phpactor#Exec(command)
    echo out
    exec "edit " . destPath
endfunction

"""""""""""""""""""""""""""
" Interactively move a file
"""""""""""""""""""""""""""
function! phpactor#MoveFile()
    let currentPath = expand('%')
    let destPath = input("Move to: ", currentPath, "file")
    let command = 'class:move ' . currentPath . ' ' . destPath
    echo "\nWARNING: This command will move the class and update ALL references in the git tree."
    echo "         It is not guaranteed to succeed. COMMIT YOUR WORK FIRST!"
    echo "NOTE: Currently buffers will not be reloaded"
    let confirm =  confirm('Do you want to proceed?', "&Yes\n&No")

    if confirm == 2
        echo "Cancelled"
        return
    endif

    let out = phpactor#Exec(command)
    echo out
    exec "edit " . destPath
endfunction

"""""""""""""""""""""""""""""""""""""""""""""""""""
" Return debug information about the current offset
"""""""""""""""""""""""""""""""""""""""""""""""""""
function! phpactor#OffsetTypeInfo()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let stdin = join(getline(1,'$'), "\n")

    let command = 'offset:info --frame stdin ' . offset
    let out = phpactor#ExecStdIn(command, stdin)

    echo out
endfunction

function! phpactor#_OffsetTypeInfo()
    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let stdin = join(getline(1,'$'), "\n")

    let command = 'offset:info --format=json stdin ' . offset
    let out = phpactor#ExecStdIn(command, stdin)

    return json_decode(out)
endfunction

""""""""""""""""""""""""
" Apply a transformation
""""""""""""""""""""""""
function! phpactor#Transform()

    " TODO: Get the list of transforms from the PHP application
    let transformations = [ 'complete_constructor', 'implement_contracts', 'add_missing_assignments' ]

    let list = []
    let c = 1
    for transformation in transformations
        let list = add(list, c . ': ' . transformation)
        let c = c + 1
    endfor
    let choice = inputlist(list)
    let transform = transformations[choice - 1]

    let offset = line2byte(line('.')) + col('.') - 1
    let stdin = join(getline(1,'$'), "\n")
    let out = phpactor#ExecStdIn('class:transform stdin --transform=' . transform, stdin)
    let savePos = getpos(".")

    if (empty(out))
        echo "No transformation made"
        return
    endif

    let @p = out
    exec "%d"
    exec ":0 put p"

    call setpos('.', savePos)
endfunction

""""""""""""""""""""""""
" Create new class
""""""""""""""""""""""""
function! phpactor#ClassNew()

    let currentPath = expand('%')
    let directory = fnamemodify(currentPath, ':h')
    let classOrPath = currentPath

    let word = expand("<cword>")

    if !empty(word)
        let offsetInfo = phpactor#_OffsetTypeInfo()
        if !empty(offsetInfo['type'])
            if offsetInfo['type'] != '<unknown>'
                let classOrPath = offsetInfo['type']
            endif
        endif
    endif

    let classOrPath = input("Create class: ", classOrPath, "file")
    echo "\n"
    let variants = phpactor#Exec('class:new --list --format=json ' . classOrPath)
    let variants = json_decode(variants)

    let list = []
    let c = 1
    for variant in variants
        let list = add(list, c . ': ' . variant)
        let c = c + 1
    endfor

    let choice = inputlist(list)
    let variant = variants[choice - 1]

    let out = phpactor#Exec('class:new --format=json --variant=' . variant . ' ' . shellescape(classOrPath))
    let out = json_decode(out)

    if out['exists'] == 1
        let confirm = confirm('File exists, overwrite?', "&Yes\n&No")

        if confirm == 2
            echo "Cancelled"
            return
        endif

        let out = phpactor#Exec('class:new --force --format=json --variant=' . variant . ' ' . shellescape(classOrPath))
        let out = json_decode(out)
    endif

    if !empty(out)
        exec ":edit " . out['path']
    endif
endfunction

""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
" Inflect new class
"
" TODO: This is copy and paste from ClassNew - need to break this out into a
"       separate "class"
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""
function! phpactor#ClassInflect()

    let currentPath = expand('%')
    let directory = fnamemodify(currentPath, ':h')
    let classOrPath = currentPath

    let word = expand("<cword>")

    if !empty(word)
        let offsetInfo = phpactor#_OffsetTypeInfo()
        if !empty(offsetInfo['type'])
            if offsetInfo['type'] != '<unknown>'
                let classOrPath = offsetInfo['type']
            endif
        endif
    endif

    let destClassOrPath = input("Inflect class to: ", classOrPath, "file")
    echo "\n"
    " call with dummy file arguments when listing
    let variants = phpactor#Exec('class:inflect --list --format=json one two') 
    let variants = json_decode(variants)

    let list = []
    let c = 1
    for variant in variants
        let list = add(list, c . ': ' . variant)
        let c = c + 1
    endfor

    let choice = inputlist(list)
    if (choice == 0)
        return 0
    endif
    let variant = variants[choice - 1]

    let command = 'class:inflect --format=json ' . shellescape(classOrPath) . ' ' . shellescape(destClassOrPath) . ' ' . variant
    let out = phpactor#Exec(command)
    let out = json_decode(out)

    if out['exists'] == 1
        let confirm = confirm('File exists, overwrite?', "&Yes\n&No")

        if confirm == 2
            echo "Cancelled"
            return
        endif

        let out = phpactor#Exec(command . ' --force')
        let out = json_decode(out)
    endif

    if !empty(out)
        exec ":edit " . out['path']
    endif
endfunction

"""""""""""""""""""""""
" Find class references
"""""""""""""""""""""""
function! phpactor#ClassReferences()

    " TODO: Delegate this look up to Phpactor
    let offsetInfo = phpactor#_OffsetTypeInfo()

    if empty(offsetInfo['type'])
        echo "Cannot determine type"
        return
    endif

    if (offsetInfo['type'] == '<unknown>')
        echo "Cannot determine type"
        return
    endif

    let class = offsetInfo['type']

    call phpactor#rpc("class_references", { "class": class })

    return
endfunction

""
" !DEPRECATED! Will be removed when everything is ported to RPC
""
function! phpactor#Exec(cmd)
    call confirm(s:phpactorInitialCwd)
    let cmd = 'php ' . s:phpactorbinpath . '--working-dir=' s:phpactorInitialCwd . ' ' . a:cmd
    let result = system(cmd)

    if (v:shell_error == 0)
        return result
    elseif (v:shell_error == 64)
        let result = json_decode(result)
        throw result['error']['message']
    else
        echo result
        throw "Could not execute command"
    endif
endfunction

""
" !DEPRECATED! Will be removed when everything is ported to RPC
""
function! phpactor#ExecStdIn(cmd, stdin)
    call confirm(s:phpactorInitialCwd)
    let cmd = 'php ' . s:phpactorbinpath . '--working-dir=' s:phpactorInitialCwd . ' ' . a:cmd
    let result = system(cmd, a:stdin)

    if (v:shell_error == 0)
        return result
    elseif (v:shell_error == 64)
        let result = json_decode(result)
        throw result['error']['message']
    else
        echo result
        throw "Could not execute command"
    endif
endfunction

function! phpactor#NamespaceInsert()
    exec ":normal! i" . phpactor#NamespaceGet()
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


"""""""""""""""""""""""
" RPC -->-->-->-->-->--
"""""""""""""""""""""""

function! phpactor#rpc(action, arguments)

    call confirm(s:phpactorInitialCwd)
    let request = {"actions": [ { "action": a:action, "parameters": a:arguments } ] }

    let cmd = 'php ' . s:phpactorbinpath . ' rpc --working-dir=' . s:phpactorInitialCwd
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

    " >> file references
    if a:actionName == "file_references"
        let list = []

        for fileReferences in a:parameters['file_references']
            for reference in fileReferences['references']
                call add(list, { 'filename': fileReferences['file'], 'lnum': reference['line_no'] })
            endfor
        endfor

        call setqflist(list)
        return
    endif

    throw "Do not know how to handle action '" . a:actionName . "'"
endfunction
