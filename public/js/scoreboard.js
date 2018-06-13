let checkedInputs = document.querySelectorAll('input[checked]');
let questionsDisplayed = document.getElementsByClassName('question').length;
let scoreCount = 0;
let questionsCount = document.getElementById('questions-count').value;
let scoreDisplay = document.getElementById('score');

// calculate score
checkedInputs.forEach(checkedInput => {
    if (!checkedInput.classList.contains('is-valid')) {
        checkedInput.classList.add('is-invalid');
    } else {
        scoreCount++;
    }
});

// display score
scoreDisplay.innerText = `${scoreCount} / ${questionsCount} (${scoreCount / questionsCount * 100}%)`;

if (questionsDisplayed !== questionsCount) {
    scoreDisplay.innerText += ` (${questionsDisplayed} questions done)`;
}