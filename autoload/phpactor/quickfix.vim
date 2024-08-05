function! phpactor#quickfix#vim(entries) abort
    call setqflist(a:entries)
    cw
endfunction

function! phpactor#quickfix#build(entries) abort
    try
      let strategy = g:phpactorQuickfixStrategy
      call call(strategy, [a:entries])
    catch /E117/
      redraw!
      echo 'The strategy "'. string(strategy) .'" is unknown, check the value of "g:phpactorQuickfixStrategy".'
    endtry
endfunction

function! phpactor#quickfix#fzf(entries) abort
    " Associate each entry data with a unique key
    let entries = {}
    " Keep track of the order of the entries by their key
    let sortedKeys = []
    for entry in a:entries
        let key = s:relative_path(entry['filename'])
            \ .':'. entry['lnum']
            \ .':'. (entry['col'])
            \ .':'. entry['text']

        let entries[key] = entry
        call add(sortedKeys, key)
    endfor

    let formattedEntries = s:align_pairs(sortedKeys, '^\(.\{-}:\d\+:\d\+:\)\s*\(.*\)\s*$', 100)

    let tmp = copy(entries)
    let entries = {}
    let source = [] " Need a list to keep the order (dict does not guarantee it)
    for key in sortedKeys
        let newKey = formattedEntries[key]
        let entries[newKey] = tmp[key]
        call add(source, newKey)
    endfor
    unlet tmp

    let actions = {
        \ 'ctrl-t': 'tab split',
        \ 'ctrl-x': 'split',
        \ 'ctrl-v': 'vsplit',
        \ 'ctrl-q': function('phpactor#quickfix#vim')
    \ }

    call fzf#run(fzf#wrap('find_references', fzf#vim#with_preview({
        \ 'source': source,
        \ 'down': '60%',
        \ '_action': actions,
        \ 'sink*': function('<SID>quickfix_sink', [entries, actions]),
        \ 'options': [
        \ '--exit-0',
        \ '--expect='. join(keys(actions), ','),
        \ '--multi',
        \ '--bind=ctrl-a:select-all,ctrl-d:deselect-all',
        \ '--inline-info',
        \ '--header', ":: Press \x1b[35mCTRL-Q\x1b[m to open the quickfix with your selection",
        \ '--delimiter=:', '--nth=1,4',
        \ '--reverse'
    \ ]}, 'up', '?'), 1))
endfunction

function! s:quickfix_sink(results, actions, lines) abort
  if 2 > len(a:lines)
    " Don't know how to handle this, should not append
    return
  endif

  let actionKey = remove(a:lines, 0)
  let Action = get(a:actions, actionKey, 'e')
  let items = map(copy(a:lines), {key, value -> a:results[value]})

  if type(function('call')) == type(Action)
    return Action(items)
  endif

  if len(a:lines) > 1
    augroup fzf_swap
      autocmd SwapExists * let v:swapchoice='o' | echohl WarningMsg
            \| echom 'fzf: E325: swap file exists: '. expand('<afile>')
            \| echohl None
    augroup END
  endif

  try
    let empty = empty(expand('%')) && 1 == line('$') && empty(getline(1)) && !&modified
    let autochdir = &autochdir
    set noautochdir

    for item in items
      let filename = fnameescape(item.filename)
      let Action = empty ? 'e' : Action " Use the current buffer if empty

      execute Action '+'.item.lnum filename
      execute 'normal!' item.col .'|'
      normal! zz

      if empty
        let empty = v:false
      endif

      if !has('patch-8.0.0177') && !has('nvim-0.2') && exists('#BufEnter')
            \ && isdirectory(item.filename)
        doautocmd BufEnter
      endif
    endfor
  catch /^Vim:Interrupt$/
  finally
    let &autochdir = autochdir
    silent! autocmd! fzf_swap
  endtry
endfunction

function! s:align_pairs(list, regexp, ...) abort
    let maxlen = 0
    let pairs = {}
    for elem in a:list
        let match = matchlist(elem, a:regexp)
        let [filename, text] = match[1:2]
        let maxlen = max([maxlen, len(filename)])
        let pairs[elem] = [filename, text]
    endfor

    let args = copy(a:000)
    let max = 60
    if 0 < len(args) && type(v:t_number) == type(args[0])
        let max = remove(args, 0)
    endif

    let maxlen = min([maxlen, max])

    return map(pairs, "printf('%-'.maxlen.'s', v:val[0]).' '.v:val[1]")
endfunction

function! s:relative_path(absolute_path)
    let l:cwd = getcwd()

    return substitute(a:absolute_path, l:cwd .'/', '', '')
endfunction

" vim: et ts=4 sw=4 fdm=marker
