let form = document.querySelector("form");
let answersBlock = document.getElementById("answers-block");
let submitAnswerBtn = document.getElementById("submit-answer");
let questionId = document.querySelector("[name=question-id]").value;
let category = document.getElementById("category").innerText;
let answerChecked = null;
let isLoggedIn = document.getElementById('is-logged-in');

answersBlock.addEventListener("change", e => {
  answerChecked = {
    isLoggedIn: isLoggedIn !== null ? isLoggedIn.value : false,
    questionId: questionId,
    answerId: e.target.id
  };
});

submitAnswerBtn.addEventListener("click", e => {
  e.preventDefault();

  if (answerChecked) {
    submitAnswerBtn.style.display = "none";

    let url = document.location.origin + "/check/" + questionId;

    fetch(url, {
      method: "POST",
      body: JSON.stringify(answerChecked),
      headers: {
        "content-type": "application/json"
      }
    })
      .then(function(response) {
        return response.json();
      })
      .then(function(myJson) {
        // deletelater
        console.log(myJson);

        let styling = "danger";
        let alertMsg = "Nope! it was: " + myJson.expected;

        if (myJson.isCorrect) {
          styling = "success";
          alertMsg = "Good answer!";
        }

        form.insertAdjacentHTML(
          "beforebegin",
          `<div class="alert alert-${styling}" role="alert">${alertMsg}</div>`
        );

        let nextUrl =
          document.location.origin +
          document.location.pathname.replace("quiz", "scoreboard");

        // check if myJson.queryString is not null (which means last question)
        if (myJson.queryString) {
          nextUrl =
            document.location.origin +
            document.location.pathname +
            myJson.queryString;
        }

        form.insertAdjacentHTML(
          "afterend",
          '<a href="' + nextUrl + '" class="btn btn-outline-primary">Next</a>'
        );
      });
  }
});
