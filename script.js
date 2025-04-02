document.getElementById("startQuiz").addEventListener("click", function() {
    alert("Get ready to quiz yourself on various topics! 🎉");
});

let timer;
let timeLeft = 30; // 30 seconds timer

function startTimer() {
    clearInterval(timer);
    timeLeft = 30;
    document.getElementById("timerDisplay").innerText = `Time Left: ${timeLeft} seconds`;
    timer = setInterval(() => {
        timeLeft--;
        document.getElementById("timerDisplay").innerText = `Time Left: ${timeLeft} seconds`;
        if (timeLeft <= 0) {
            clearInterval(timer);
            alert("Time's up!");
        }
    }, 1000);
}

function showScore() {
    const score = Math.floor(Math.random() * 100);
    document.getElementById("scoreDisplay").innerText = `Your Score: ${score}`;
}

function showLeaderboard() {
    const leaderboard = "1. Alice - 90\n2. Bob - 85\n3. Charlie - 80";
    document.getElementById("leaderboardDisplay").innerText = `Leaderboard:\n${leaderboard}`;
}


const { MongoClient, ServerApiVersion } = require('mongodb');
const uri = "mongodb+srv://jmvr2023184464129:WoxbvytE1HQDROjI@cluster0.atmmnbn.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";

// Create a MongoClient with a MongoClientOptions object to set the Stable API version
const client = new MongoClient(uri, {
  serverApi: {
    version: ServerApiVersion.v1,
    strict: true,
    deprecationErrors: true,
  }
});

async function run() {
  try {
    // Connect the client to the server	(optional starting in v4.7)
    await client.connect();
    // Send a ping to confirm a successful connection
    await client.db("admin").command({ ping: 1 });
    console.log("Pinged your deployment. You successfully connected to MongoDB!");
  } finally {
    // Ensures that the client will close when you finish/error
    await client.close();
  }
}
run().catch(console.dir);


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>