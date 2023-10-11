import mitt from 'mitt'

export const FILE_UPLOAD_STARTED = 'FILE_UPLOAD_STARTED'

export const SHOW_ERROR_DIALOG = 'SHOW_ERROR_DIALOG'

export const SHOW_SUCCESS_NOTIFICATION = 'SHOW_SUCCESS_NOTIFICATION'

export const SHOW_PROGRESS_BAR = 'SHOW_PROGRESS_BAR'

export const emitter = mitt()

export function showErrorDialog(message) {
    emitter.emit(SHOW_ERROR_DIALOG, {message})
}

export function showSuccessNotification(message){
    emitter.emit(SHOW_SUCCESS_NOTIFICATION, {type: 'success', message})
}

export function showErrorNotification(message){
    emitter.emit(SHOW_ERROR_NOTIFICATION , {type: 'error', message})
}