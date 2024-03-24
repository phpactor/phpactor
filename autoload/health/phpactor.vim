function! s:check_info(status) abort
  call v:lua.vim.health.start('Info')

  call v:lua.vim.health.info('Phpactor version: '. a:status.phpactor_version)
  call v:lua.vim.health.info('PHP version: '. a:status.php_version)
  call v:lua.vim.health.info('Filesystems'. join(a:status.filesystems, ', '))
  call v:lua.vim.health.info('Working directory'. a:status.cwd)
endfunction

function! s:check_diagnostics(diagnostics) abort
  call v:lua.vim.health.start('Diagnostics')

  for [l:diagnostic, l:isOk] in items(a:diagnostics)
    if l:isOk
      call v:lua.vim.health.ok(l:diagnostic)
    else
      call v:lua.vim.health.warn(l:diagnostic)
    endif
  endfor
endfunction

function! s:check_config_files(configFiles) abort
  call v:lua.vim.health.start('Config files (missing is not bad)')

  for [l:configFile, l:isOk] in items(a:configFiles)
    if l:isOk
      call v:lua.vim.health.ok(l:configFile)
    else
      call v:lua.vim.health.warn(l:configFile)
    endif
  endfor
endfunction

function! health#phpactor#check() abort
  let l:status = phpactor#rpc('status', {'type': 'detailed'})

  call s:check_info(l:status)
  call s:check_diagnostics(l:status.diagnostics)
  call s:check_config_files(l:status.config_files)
endfunction
