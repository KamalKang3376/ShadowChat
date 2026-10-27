import { initializeApp } from "https://www.gstatic.com/firebasejs/13.0.0/firebase-app.js";
import {
    getDatabase,
    ref,
    push,
    serverTimestamp
} from "https://www.gstatic.com/firebasejs/13.0.0/firebase-database.js";

const firebaseConfig = {
  apiKey: "AIzaSyDhTVWf1TBiJv7eL-Zrfxqwu5IZbeQxoCs",
  authDomain: "shadowchat-98c63.firebaseapp.com",
  databaseURL: "https://shadowchat-98c63-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "shadowchat-98c63",
  storageBucket: "shadowchat-98c63.firebasestorage.app",
  messagingSenderId: "639194484694",
  appId: "1:639194484694:web:3a1075f53ec251b88cde77"
};

const app = initializeApp(firebaseConfig);
const database = getDatabase(app);

console.log("Firebase connected successfully!");

const roomCode = window.shadowChatRoom;