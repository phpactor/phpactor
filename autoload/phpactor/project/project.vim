function! phpactor#project#project#createFromRootPath(primaryRootPath) abort
  return {
        \ 'primaryRootPath': s:normalizeRootPath(a:primaryRootPath),
        \ 'containsFile': function('s:containsFile')
        \ }
endfunction

function! s:containsFile(filepath) dict
  let l:path = simplify(fnamemodify(a:filepath, ':p:h'))

  return phpactor#fileutils#isSubdir(l:path, l:self.primaryRootPath)
endfunction

function s:normalizeRootPath(path) abort
  let l:path = phpactor#fileutils#normalizePath(a:path)

  " @todo better check if it is not an existing directory
  if ! isdirectory(l:path)
    throw printf('Path "%s" does not exist or is not a directory so it cannot be a root path.', l:path)
  endif

  return l:path
endfunction
