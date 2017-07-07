"  ______    __    __  .______      ___       ______ .___________.  ______   .______      
" |   _  \  |  |  |  | |   _  \    /   \     /      ||           | /  __  \  |   _  \     
" |  |_)  | |  |__|  | |  |_)  |  /  ^  \   |  ,----'`---|  |----`|  |  |  | |  |_)  |    
" |   ___/  |   __   | |   ___/  /  /_\  \  |  |         |  |     |  |  |  | |      /     
" |  |      |  |  |  | |  |     /  _____  \ |  `----.    |  |     |  `--'  | |  |\  \----.
" | _|      |__|  |__| | _|    /__/     \__\ \______|    |__|      \______/  | _| `._____|
"                                                                                         

let s:genpath = expand('<sfile>:p:h') . '/../bin/phpactor'

function! phpactor#NamespaceGet()
    let currentPath = expand('%')
    let command = 'file:info --format=json ' . currentPath
    let out = phpactor#Exec(command)
    let results = json_decode(out)

    return results['class_namespace']
endfunction

""""""""""""""""""""""""
" Autocomplete
""""""""""""""""""""""""
function! phpactor#Complete(findstart, base)

    if a:findstart
        let line = getline('.')
        let start = col('.') - 1

        while start > 0 && (line[start - 1] =~ '\a' || line[start - 1] == '$')
            let start -= 1
        endwhile

        return start
    endif

    let base = getline('.')
    let matched = matchstr(base, "->")

    if (!match(base, "->" && !match(base, "::")))
        return -2
    endif

    let offset = line2byte(line(".")) + col(".") + strlen(a:base) - 4
    let stdin = join(getline(1,'.'), "\n")
    let stdin = stdin . a:base
    let stdin = stdin . "\n" . join(getline(line('.') + 1, '$'), "\n")

    let command = 'file:offset --format=json stdin ' . offset
    let results = phpactor#ExecStdIn(command, stdin)
    let results = json_decode(results)

    if (results['type'] == '<unknown>')
        echo "Type could not be determined"
        return -2
    endif

    let command = 'class:reflect --format=json ' . shellescape(results['type'])
    let reflection = phpactor#Exec(command)
    let reflection = json_decode(reflection)

    let completions = []

    if !empty(reflection['methods'])
        for method in values(reflection['methods'])
            let info = method['synopsis']
            call add(completions, { 'word': a:base . method['name'], 'info': info, 'kind': 'f'})
        endfor
    endif

    if !empty(reflection['properties'])
        for property in values(reflection['properties'])
            call add(completions, { 'word': a:base . property['name'], 'info': property['info'], 'kind': 'm'})
        endfor
    endif

    if !empty(reflection['constants'])
        for constant in values(reflection['constants'])
            call add(completions, { 'word': a:base . constant['name'], 'info': '', 'kind': 'm'})
        endfor
    endif

    return completions
endfunc

""""""""""""""""""""""""
" Insert a use statement
""""""""""""""""""""""""
function! phpactor#UseAdd()

    ""
    " @return int Number of extra lines added
    ""
    function! UseAdd(savePos)
        " START: Resolve FQN for class
        let word = expand("<cword>")

        let out = phpactor#Exec('class:search --format=json ' . word)
        let results = json_decode(out)

        if (len(results) == 0)
            echo "Could not find class"
            echo results
            return 0
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
                return 0
            endif
            let choice = choice - 1

            let classInfo = get(results, choice, {})

            if ({} == classInfo)
                echo "Invalid choice"
                return 0
            endif
        endif

        if (len(results) == 1)
            let classInfo = results[0]
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
function! phpactor#GotoType()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let currentPath = expand('%')

    let command = 'file:offset --format=json ' . currentPath . ' ' . offset
    let out = phpactor#Exec(command)
    let results = json_decode(out)

    if (empty(results['path']))
        echo "Could not locate class at offset: " . offset
        return
    endif

    exec "edit " . results['path']

endfunction

"""""""""""""""""""""""""""""""""""
" Return type information at offset
"""""""""""""""""""""""""""""""""""
function! phpactor#ReflectAtOffset()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let stdin = join(getline(1,'$'), "\n")

    let command = 'file:offset --format=json stdin ' . offset
    let out = phpactor#ExecStdIn(command, stdin)
    let results = json_decode(out)

    echo results
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
    echo "         It is not guranteed to succeed. COMMIT YOUR WORK FIRST!"
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

    let command = 'file:offset --frame stdin ' . offset
    let out = phpactor#ExecStdIn(command, stdin)

    echo out
endfunction

"""""""""""""""""""""
" Complete contructor
"""""""""""""""""""""
function! phpactor#CompleteConstructor()

    " START: Resolve FQN for class
    let offset = line2byte(line('.')) + col('.') - 1
    let stdin = join(getline(1,'$'), "\n")
    let out = phpactor#ExecStdIn('class:transform stdin --transform=complete_constructor', stdin)
    let savePos = getpos(".")

    if (empty(out))
        echo "No transformation made"
        return
    endif

    let @+ = out
    exec "%d"
    exec ":0 put +"

    call setpos('.', savePos)
endfunction

function! phpactor#Exec(cmd)
    return phpactor#ExecStdIn(a:cmd, '')
endfunction

function! phpactor#ExecStdIn(cmd, stdin)
    let result = system('php ' . s:genpath . ' --verbose ' . a:cmd, a:stdin)

    if (v:shell_error == 0)
        return result
    else 
        throw result
    endif
endfunction

function! phpactor#NamespaceInsert()
    exec ":normal! i" . phpactor#NamespaceGet()
endfunction

