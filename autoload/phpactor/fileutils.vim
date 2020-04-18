function! phpactor#fileutils#searchUpwardBySingleMarker(path, marker) abort
  let l:markerFile = findfile(a:marker, fnamemodify(a:path, ':p:h').';')

  return empty(l:markerFile) ? v:null : fnamemodify(l:markerFile, ':p:h')
endfunction

function! phpactor#fileutils#searchDirectoryUpwardByRootMarkers(initialDirectory, workspaceRootMarkers, forbiddenDirs)
  for l:marker in a:workspaceRootMarkers
    let l:markerPath = phpactor#fileutils#searchUpwardBySingleMarker(a:initialDirectory, l:marker)

    if v:null is l:markerPath
      continue
    endif

    if empty(a:forbiddenDirs) || ! phpactor#fileutils#containsDirectoryFrom(l:markerPath, a:forbiddenDirs)
      return l:markerPath
    endif
  endfor

  return v:null
endfunction

function! phpactor#fileutils#normalizePath(path) abort
  let l:modifiers = isdirectory(a:path) ? ':p:h' : ':p'

  return simplify(fnamemodify(a:path, l:modifiers))
endfunction

function! phpactor#fileutils#containsDirectoryFrom(directory, dirList) abort
  let l:directory = phpactor#fileutils#normalizePath(a:directory)

  for l:subdir in a:dirList
    let l:subdir = phpactor#fileutils#normalizePath(l:subdir)
    if phpactor#fileutils#isSubdir(l:subdir, l:directory)
      return v:true
    endif
  endfor

  return v:false
endfunction

function! phpactor#fileutils#isSubdir(expectedSubdir, directory) abort
  let l:expectedSubdir = phpactor#fileutils#normalizePath(a:expectedSubdir)
  let l:directory = phpactor#fileutils#normalizePath(a:directory)

  return stridx(l:expectedSubdir, l:directory) >= 0
endfunction
