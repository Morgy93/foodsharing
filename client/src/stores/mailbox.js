import { reactive } from 'vue'

export const MAILBOX_PAGE = Object.freeze({
  EMAIL_LIST: 1,
  READ_EMAIL: 2,
  NEW_EMAIL: 3,
})

export const MAILBOX_FOLDER = Object.freeze({
  INBOX: 1,
  SENT: 2,
  TRASH: 3,
})

export const store = {
  state: reactive({
    page: null,
    answerMode: false,
    selectedMailbox: [],
  }),
  setPage (value) {
    this.state.page = value
  },
  setAnswerMode (value) {
    this.state.answerMode = value
  },
  setMailbox (mailboxId, mailboxName, folderId) {
    this.state.selectedMailbox = [mailboxId, mailboxName, folderId]
  },
}
