import { get, patch, post } from './base'

export function getQuizStatus (quizId) {
  return get(`/quiz/${quizId}/status`)
}

export function getQuizResults (quizId) {
  return get(`/quiz/${quizId}/results`)
}

export function getQuestion (quizId) {
  return get(`/quiz/${quizId}/question`)
}

export function startQuiz (quizId, timed = true) {
  return post(`/quiz/${quizId}/start`, { timed })
}

export function answerQuestion (quizId, selectedAnswers) {
  return post(`/quiz/${quizId}/answer`, { answers: selectedAnswers })
}

export function commentQuestion (questionId, text) {
  return post(`/question/${questionId}/comment`, { text })
}

export function getQuiz (quizId) {
  return get(`/quiz/${quizId}`)
}

export function getQuestions (quizId) {
  return get(`/quiz/${quizId}/questions`)
}

export function getQuestionComments (questionId) {
  return get(`/quiz/${questionId}/comments`)
}

export function editQuiz (quizId, data) {
  return patch(`/quiz/${quizId}`, data)
}

export function editQuestion (questionId, data) {
  return patch(`/question/${questionId}`, data)
}
