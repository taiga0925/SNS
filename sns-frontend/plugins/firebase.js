import firebase from 'firebase/app'
import 'firebase/auth'

// ここをご自身のFirebase設定に書き換えてください
const config = {
  apiKey: "AIzaSyCoWYRn4A1ume3Ba7vPR-Q8w5KDiaVMD_U",
  authDomain: "sns-app-ee94c.firebaseapp.com",
  projectId: "sns-app-ee94c",
  storageBucket: "sns-app-ee94c.firebasestorage.app",
  messagingSenderId: "505013258913",
  appId: "1:505013258913:web:4934f71df4fd05d559bee0",
  measurementId: "G-PT0JEBHNTD"
}

// 二重初期化を防ぐためのチェック
if (!firebase.apps.length) {
  firebase.initializeApp(config)
}

export default firebase

