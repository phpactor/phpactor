function! s:check_info(status) abort
  call health#report_start('Info')

  call health#report_info('Phpactor version: '. a:status.phpactor_version)
  call health#report_info('PHP version: '. a:status.php_version)
  call health#report_info('Filesystems'. join(a:status.filesystems, ', '))
  call health#report_info('Working directory'. a:status.cwd)
endfunction

function! s:check_diagnostics(diagnostics) abort
  call health#report_start('Diagnostics')

  for [l:diagnostic, l:isOk] in items(a:diagnostics)
    if l:isOk
      call health#report_ok(l:diagnostic)
    else
      call health#report_warn(l:diagnostic)
    endif
  endfor
endfunction

function s:check_prompt_from_autocommand() abort
  call health#report_start('Project root detection')
  let l:shmFIsPresent = (stridx(&shortmess, 'F') >= 0)

  if !l:shmFIsPresent
    call health#report_ok('`shortmess` does not contain `F`')
    return
  endif

  call health#report_warn(printf('`shortmess` vim option contains `F` (is %s)', &shortmess))

  if v:true == g:phpactorAllowInteractiveProjectResolution
    call health#report_warn('- interactive choice of method may be unavailable.')
  endif

  if index(g:phpactorNoninteractiveProjectResolvers, 'manual') >= 0
    call health#report_warn('- manual input was enabled but it may not work')
  endif
endfunction

function! s:check_config_files(configFiles) abort
  call health#report_start('Config files (missing is not bad)')

  for [l:configFile, l:isOk] in items(a:configFiles)
    if l:isOk
      call health#report_ok(l:configFile)
    else
      call health#report_warn(l:configFile)
    endif
  endfor
endfunction

function! health#phpactor#check() abort
  let l:status = phpactor#rpc('status', {'type': 'detailed'})

  call s:check_info(l:status)
  call s:check_diagnostics(l:status.diagnostics)
  call s:check_config_files(l:status.config_files)
  call s:check_prompt_from_autocommand()
endfunction
