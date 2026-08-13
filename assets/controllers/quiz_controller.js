import { Controller } from '@hotwired/stimulus';
import { trackEvent } from '../analytics.js';

/*
 * Game-show style quiz player. All 20 questions (with their correct answer)
 * are embedded in the page up front by the server, so the whole game runs
 * client-side with no reload between questions — a "rejouer" simply
 * navigates back to /quiz/jouer, which draws a brand new random set.
 */
export default class extends Controller {
    static targets = [
        'progress', 'score', 'category', 'question', 'options', 'nextButton',
        'card', 'actions', 'result', 'resultTitle', 'resultScore',
    ];
    static values = { questions: Array };

    connect() {
        this.index = 0;
        this.score = 0;
        this.answered = false;
        this.renderQuestion();
    }

    renderQuestion() {
        const total = this.questionsValue.length;
        const q = this.questionsValue[this.index];

        this.progressTarget.textContent = `Question ${this.index + 1}/${total}`;
        this.scoreTarget.textContent = `Score : ${this.score}`;
        this.categoryTarget.textContent = q.category || '';
        this.questionTarget.textContent = q.question;
        this.nextButtonTarget.hidden = true;
        this.answered = false;

        this.optionsTarget.replaceChildren();
        // Shuffle the display order so the correct answer's letter never
        // gives away a positional pattern, regardless of how the source
        // question bank happens to be distributed.
        const shuffled = Object.entries(q.options).sort(() => Math.random() - 0.5);
        shuffled.forEach(([letter, text]) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'quiz-option';
            btn.dataset.letter = letter;
            btn.setAttribute('data-action', 'click->quiz#answer');
            btn.innerHTML = `<span class="quiz-option__letter">${letter}</span><span class="quiz-option__text">${text}</span>`;
            this.optionsTarget.appendChild(btn);
        });
    }

    answer(event) {
        if (this.answered) {
            return;
        }
        this.answered = true;

        const chosen = event.currentTarget.dataset.letter;
        const q = this.questionsValue[this.index];
        const buttons = this.optionsTarget.querySelectorAll('.quiz-option');

        buttons.forEach((btn) => {
            btn.disabled = true;
            if (btn.dataset.letter === q.correctOption) {
                btn.classList.add('is-correct');
            } else if (btn.dataset.letter === chosen) {
                btn.classList.add('is-wrong');
            }
        });

        if (chosen === q.correctOption) {
            this.score += 1;
            this.scoreTarget.textContent = `Score : ${this.score}`;
        }

        this.nextButtonTarget.hidden = false;
    }

    next() {
        this.index += 1;

        if (this.index >= this.questionsValue.length) {
            this.showResult();
        } else {
            this.renderQuestion();
        }
    }

    showResult() {
        this.cardTarget.hidden = true;
        this.actionsTarget.hidden = true;
        this.resultTarget.hidden = false;

        const total = this.questionsValue.length;
        const ratio = this.score / total;
        let title = 'À revoir !';
        if (ratio >= 0.8) {
            title = 'Excellent !';
        } else if (ratio >= 0.5) {
            title = 'Bien joué !';
        }

        this.resultTitleTarget.textContent = title;
        this.resultScoreTarget.textContent = `Score final : ${this.score}/${total}`;

        trackEvent('quiz_completed', { score: this.score, total });
    }
}
