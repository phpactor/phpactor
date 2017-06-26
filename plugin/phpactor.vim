" phpnamespace - Composer namepspace generator
"
" Author: Daniel Leech <daniel@dantleech.com>

let s:genpath = expand('<sfile>:p:h') . '/../bin/phpactor'

function! PhactNamespaceGet()
    let currentPath = expand('%')
    let command = 'file:info --format=json ' . currentPath
    let out = PhactExec(command)
    let results = json_decode(out)

    return results['class_namespace']
endfunction

function! PhactUseAdd()
    let word = expand("<cword>")

    let command = 'class:search --format=json ' . word
    let out = PhactExec(command)
    let results = json_decode(out)

    if (len(results) == 0)
        echo "Could not find class"
        echo results
        return
    endif

    if (len(results) > 1)
        let c = 1
        for info in results
            echo c . ": " . info['class']
            let c = c + 1
        endfor

        let choice = input('Choose: ')
        let choice = choice - 1

        let classInfo = get(results, choice, {})

        if ({} == classInfo)
            echo "Invalid choice"
            return
        endif
    endif

    if (len(results) == 1)
        let classInfo = results[0]
    endif

    call cursor(1, 1)
    let existing = search('^.*use.*\\' . classInfo['class_name'] . ';$')

    echo "\n"
    if (existing > 0)
        echo "Use staement already included on line " . existing
        return
    endif

    call cursor(1, 1)
    let useLineNb = search('^use')
    while (useLineNb)
        let useLineNb = search('^use')
        echo useLineNb
    endwhile



endfunction

function! PhactExec(cmd)
    let result = system('php ' . s:genpath . ' ' . a:cmd)

    if (v:shell_error == 0)
        return result
    else 
        echoerr result
    endif
endfunction

function! PhactNamespaceInsert()
    exec "normal! i" . PhactNamespaceGet()
endfunction

