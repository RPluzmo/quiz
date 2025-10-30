class QuizManager {
  constructor() {
    this.current = 0;
    this.answers = {};
    this.questions = [];
  }

  init(questions) {
    this.questions = questions || [];
    this.current = 0;
    this.answers = {};
    this.renderQuestion();
    this.updateProgress();
  }

  renderQuestion() {
    const q = this.questions[this.current];
    const container = document.getElementById("question-container");
    if (!q || !container) return;

    let html = `
      <div class="question-text">
        <strong>${this.current + 1}. jautājums no ${this.questions.length}</strong>
        <p>${q.text}</p>
      </div>
      <ul class="answers-list">
        ${q.answers
          .map(
            (a) => `
          <li class="answer-option ${this.answers[q.id] === a.id ? "selected" : ""}" data-qid="${q.id}" data-aid="${a.id}">
            <input type="radio" name="answer-${q.id}" value="${a.id}" ${
              this.answers[q.id] === a.id ? "checked" : ""
            }>
            <label>${a.text}</label>
          </li>
        `
          )
          .join("")}
      </ul>
    `;
    container.innerHTML = html;
    this.updateNavButtons();
  }

  selectAnswer(qid, aid) {
    this.answers[qid] = aid;
    // highlight selected
    document.querySelectorAll(".answer-option").forEach((opt) => {
      if (parseInt(opt.dataset.qid) === qid) {
        opt.classList.toggle("selected", parseInt(opt.dataset.aid) === aid);
        const radio = opt.querySelector('input[type="radio"]');
        if (radio) radio.checked = parseInt(opt.dataset.aid) === aid;
      }
    });
    this.updateNavButtons();
  }

  updateProgress() {
    const bar = document.getElementById("progress-bar");
    const txt = document.getElementById("progress-text");
    if (!bar || !txt) return;
    const percent = ((this.current + 1) / this.questions.length) * 100;
    bar.style.width = percent + "%";
    bar.textContent = Math.round(percent) + "%";
    txt.textContent = `Question ${this.current + 1} of ${this.questions.length}`;
  }

  updateNavButtons() {
    const prev = document.getElementById("prev-btn");
    const next = document.getElementById("next-btn");
    const submit = document.getElementById("submit-btn");
    const qid = this.questions[this.current]?.id;
    const answered = this.answers[qid] !== undefined;

    if (prev) prev.disabled = this.current === 0;
    if (next && submit) {
      const last = this.current === this.questions.length - 1;
      next.style.display = last ? "none" : "inline-block";
      submit.style.display = last ? "inline-block" : "none";
      next.disabled = !answered;
      submit.disabled = !answered;
    }
  }

  next() {
    if (this.current < this.questions.length - 1) {
      this.current++;
      this.renderQuestion();
      this.updateProgress();
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  }

  prev() {
    if (this.current > 0) {
      this.current--;
      this.renderQuestion();
      this.updateProgress();
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  }

  submit() {
    const total = this.questions.length;
    const answered = Object.keys(this.answers).length;
    const form = document.getElementById("quiz-form");
    if (!form) return;

    if (answered < total) {
      const proceed = confirm(
        `Jūs nēsat atbildējis uz ${total - answered} jautājumiem.. Vai iesniegt?`
      );
      if (!proceed) return;
    }

    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "answers";
    input.value = JSON.stringify(this.answers);
    form.appendChild(input);
    form.submit(); // normal PHP post
  }
}

const quizManager = new QuizManager();

// ---------- PAGE INITIALIZATION ----------
document.addEventListener("DOMContentLoaded", () => {
  console.log("jankas skripts itkaa straadaa");

  // QUIZ card click → open quiz
  document.querySelectorAll(".quiz-card").forEach((card) => {
    card.addEventListener("click", () => {
      const id = card.dataset.quizId;
      if (id) location.href = `take_quiz.php?id=${id}`;
    });
  });

  // QUIZ events (answers + navigation)
  const qContainer = document.getElementById("question-container");
  if (qContainer) {
    qContainer.addEventListener("click", (e) => {
      const li = e.target.closest(".answer-option");
      if (li) {
        const qid = parseInt(li.dataset.qid);
        const aid = parseInt(li.dataset.aid);
        quizManager.selectAnswer(qid, aid);
      }
    });
  }
  const prev = document.getElementById("prev-btn");
  const next = document.getElementById("next-btn");
  const submit = document.getElementById("submit-btn");
  if (prev) prev.addEventListener("click", () => quizManager.prev());
  if (next) next.addEventListener("click", () => quizManager.next());
  if (submit) submit.addEventListener("click", () => quizManager.submit());

  // KEYBOARD NAVIGATION
  document.addEventListener("keydown", (e) => {
    if (!document.getElementById("question-container")) return;
    if (e.key === "ArrowLeft") quizManager.prev();
    if (e.key === "ArrowRight") quizManager.next();
  });

  // PASSWORD STRENGTH CHECKER
  const pw = document.getElementById("password");
  const feedback = document.getElementById("password-feedback");
  if (pw && feedback) {
    pw.addEventListener("input", () => {
      const val = pw.value;
      const errors = [];
      if (val.length < 9) errors.push("Parolei jāsastāv vismaz no 9 simboliem, to skaitā:");
      if (!/[A-Z]/.test(val)) errors.push("vismaz 1 DRUKĀTAIS burts");
      if (!/[a-z]/.test(val)) errors.push("Vismaz 1 parastais burts");
      if (!/[0-9]/.test(val)) errors.push("vismaz viens skaitlis");
      if (!/[^A-Za-z0-9]/.test(val)) errors.push("vismaz 1 simbols");
      if (errors.length)
        feedback.innerHTML =
          "<ul style='color:#e74c3c'>" +
          errors.map((e) => `<li>${e}</li>`).join("") +
          "</ul>";
      else
        feedback.innerHTML =
          "<p style='color:#27ae60'>Parole atbilst</p>";
    });
  }

  // AUTO-HIDE ALERTS
  document.querySelectorAll(".alert").forEach((a) => {
    setTimeout(() => {
      a.style.transition = "opacity .5s";
      a.style.opacity = "0";
      setTimeout(() => a.remove(), 500);
    }, 4000);
  });

  // TABLE SEARCH (Admin users)
  const searchInput = document.getElementById("user-search");
  const usersTable = document.getElementById("users-table");
  if (searchInput && usersTable) {
    searchInput.addEventListener("keyup", () => {
      const val = searchInput.value.toLowerCase();
      usersTable.querySelectorAll("tbody tr").forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(val)
          ? ""
          : "none";
      });
    });
  }

  // TOOLTIP fallback
  document
    .querySelectorAll("[data-tooltip]")
    .forEach((el) => (el.title = el.dataset.tooltip));

  // LOGOUT CONFIRM if needed
  const logoutLink = document.querySelector('a[href="logout.php"]');
  if (logoutLink) {
    logoutLink.addEventListener("click", (e) => {
      if (!confirm("Vai izrakstīties?")) e.preventDefault();
    });
  }
});
