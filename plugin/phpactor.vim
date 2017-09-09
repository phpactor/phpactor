"  ______    __    __  .______      ___       ______ .___________.  ______   .______      
" |   _  \  |  |  |  | |   _  \    /   \     /      ||           | /  __  \  |   _  \     
" |  |_)  | |  |__|  | |  |_)  |  /  ^  \   |  ,----'`---|  |----`|  |  |  | |  |_)  |    
" |   ___/  |   __   | |   ___/  /  /_\  \  |  |         |  |     |  |  |  | |      /     
" |  |      |  |  |  | |  |     /  _____  \ |  `----.    |  |     |  `--'  | |  |\  \----.
" | _|      |__|  |__| | _|    /__/     \__\ \______|    |__|      \______/  | _| `._____|
"                                                                                         

let s:phpactorpath = expand('<sfile>:p:h') . '/..'
let s:phpactorbinpath = s:phpactorpath. '/bin/phpactor'

function! phpactor#NamespaceGet()
    let currentPath = expand('%')
    let command = 'file:info --format=json ' . currentPath
    let out = phpactor#Exec(command)
    let results = json_decode(out)

    return results['class_namespace']
endfunction

function! phpactor#_searchAndSelectClassInfo()
    " START: Resolve FQN for class
    let word = expand("<cword>")

    let out = phpactor#Exec('class:search --format=json ' . word)
    let results = json_decode(out)

    if (len(results) == 0)
        echo "Could not find class"
        echo results
        return {}
    endif

    if (len(results) > 1)
        let c = 1
        let height = len(results) + 1
        let list = []
        for info in results
            let list = add(list, c . '. ' . info['class'])
            let c = c + 1
        endfor

        let choice = inputlist(list)
        if (choice == 0)
            return {}
        endif
        let choice = choice - 1

        let classInfo = get(results, choice, {})

        if ({} == classInfo)
            echo "Invalid choice"
            return {}
        endif
    endif

    if (len(results) == 1)
        let classInfo = results[0]
    endif

    return classInfo
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
    let stdin = join(getline(1,'.'), "\n")
    let stdin = stdin . "\n" . join(getline(line('.') + 1, '$'), "\n")

    let results = phpactor#ExecStdIn('complete --format=json stdin ' . offset, stdin)
    let results = json_decode(results)

    let completions = []

    if !empty(results['suggestions'])
        for suggestion in results['suggestions']
            call add(completions, { 'word': suggestion['name'], 'menu': suggestion['info'], 'kind': suggestion['type']})
        endfor
    endif

    return completions
endfunc

""""""""""""""""""""""""
" Expand a use statement
""""""""""""""""""""""""
function! phpactor#ClassExpand()
    let classInfo = phpactor#_searchAndSelectClassInfo()
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

        let classInfo = phpactor#_searchAndSelectClassInfo()

        if (empty(classInfo))
            return
        endif

        call cursor(1, 1)
        let existing = search('^.*use.*\\' . classInfo['class_name'] . ';$')

        if (existing > 0)
            echo "\n"
            echo "Use statement already included on line:" . existing
            call setpos('.', a:savePos)
            return 0
        endif
        "END: Resolve FQN for class

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

""""""""""""""""
" Goto defintion
""""""""""""""""
function! phpactor#GotoDefinition()
    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let currentPath = expand('%')

    let command = 'offset:definition --format=json ' . currentPath . ' ' . offset

    try
        let out = phpactor#Exec(command)
        let result = json_decode(out)
    catch
        echo v:exception
        return
    endtry

    call phpactor#switchToBufferOrEdit(result['path'])
    exec ':goto ' . (result['offset'] + 1)
    normal! zz
endfunction

function! phpactor#switchToBufferOrEdit(filePath)
    let bufferNumber = bufnr(a:filePath . '$')

    if (bufferNumber == -1)
        exec ":edit " . a:filePath
        return
    endif

    exec ":buffer " . bufferNumber
endfunction

""""""""""""""""
" Goto type
""""""""""""""""
function! phpactor#GotoType()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let currentPath = expand('%')

    let command = 'offset:info --format=json ' . currentPath . ' ' . offset
    let out = phpactor#Exec(command)
    let results = json_decode(out)

    if (empty(results['type_path']))
        echo "Could not locate class at offset: " . offset
        return
    endif

    exec "edit " . results['type_path']

endfunction

"""""""""""""""""""""""""""""""""""
" Return type information at offset
"""""""""""""""""""""""""""""""""""
function! phpactor#ReflectAtOffset()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let stdin = join(getline(1,'$'), "\n")

    let command = 'offset:info --format=json stdin ' . offset
    let out = phpactor#ExecStdIn(command, stdin)
    let results = json_decode(out)

    if (results['type'] == "<unknown>")
        echo "Could not locate class at offset: " . offset
        return
    endif

    let command = 'class:reflect ' . shellescape(results['type'])
    let out = phpactor#Exec(command)
    echo out

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

    let offsetInfo = phpactor#_OffsetTypeInfo()
    let this = {}

    function! this.populateQuickFix(class)
        let out = phpactor#Exec('references:class --format=json ' . shellescape(a:class))
        let results = json_decode(out)

        let list = []

        for fileReferences in results['references']
            for reference in fileReferences['references']
                call add(list, { 'filename': fileReferences['file'], 'lnum': reference['line_no'] })
            endfor
        endfor

        call setqflist(list)
        exec ":cc 1"
    endfunction

    function! this.showReferences(class)
        let out = phpactor#Exec('references:class --no-ansi ' . shellescape(a:class))
        echo out
    endfunction

    if empty(offsetInfo['type'])
        echo "Cannot determine type"
        return
    endif

    if (offsetInfo['type'] == '<unknown>')
        echo "Cannot determine type"
        return
    endif

    let class = offsetInfo['type']
    let options = [ "1. List", "2. Quickfix" ]
    let choice = inputlist(options)

    if (1 == choice)
        call this.showReferences(class)
    endif

    if (2 == choice)
        call this.populateQuickFix(class)
    endif

endfunction

function! phpactor#Exec(cmd)
    let cmd = 'php ' . s:phpactorbinpath . ' ' . a:cmd
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

function! phpactor#ExecStdIn(cmd, stdin)
    let cmd = 'php ' . s:phpactorbinpath . ' ' . a:cmd
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
" RPC -->-->-->-->-->--
"""""""""""""""""""""""

function! phpactor#rpc(action, arguments)

    let request = {"actions": [ { "action": a:action, "parameters": a:arguments } ] }

    let cmd = 'php ' . s:phpactorbinpath . ' rpc'
    let result = system(cmd, json_encode(request))

    if (v:shell_error == 0)
        let result = json_decode(result)

        for action in result['actions']
            let actionName = action['action']
            let parameters = action['parameters']

            if actionName == "echo"
                echo action["parameters"]["message"]
                continue
            endif
        endfor
    else
        echo "Phpactor returned an error: " . result
        return
    endif
endfunction
