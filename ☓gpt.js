async function submitPrompt() {
    const prompt = document.getElementById("inputText").value;
    const outputElement = document.getElementById("outputText");
    try {
      const URL = "https://api.openai.com/v1/chat/completions";
      const KEY = "secret";
      const systemPrompt = `
            あなたは悩み解決のための本を優しく紹介するガイドです。
            以下の制約条件を厳密に守ってロールプレイを行ってください。

            制約条件:
            ・ユーザーの悩みや困りごとに対して解決のヒントとなる本を紹介します。
            ・やさしくて安心できる雰囲気を持ち、親しみやすい言葉で対応します。
            ・丁寧で理解しやすい説明を心がけ、ユーザーがリラックスできるよう努めます。
            ・ユーザーの要望に応じて質問し、適切な本を提案します。
            ・専門的な知識を持ちつつ、ユーザーに寄り添う態度を大切にします。
            ・ユーザーの悩みをよく聞き、具体的なアドバイスと関連する本を紹介します。
            ・新入社員が仕事の量に悩む場合、『ゲーデルの不完全性定理』などの間接的なヒントとなる本を紹介します。
            ・ビジネス関連の悩みには、数学書や漫画など他分野の本を紹介します。
            ・少なくとも3冊は本を紹介します。
            ・プログラミングスクールの学生が卒業制作に悩んでいる場合、『ブルーピリオド』などを紹介します。
            ・インターネットで似た悩みを調査し、その悩みを解決した本を紹介します。
            `
      
      const response = await fetch(URL, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${KEY}`,
        },
        body: JSON.stringify({
          model: "gpt-3.5-turbo",
          messages: [{ role: "system", content:systemPrompt},
                     { role: "user", content: prompt }],
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      outputElement.textContent = data.choices[0].message.content; // APIの応答に応じて適切なパスを選択

      // テキストエリアの値をクリアする
      document.getElementById("inputText").value = "";

    } catch (e) {
      outputElement.textContent = "Error: " + e.message;
    }
}


const btn = document.getElementById("btn");
const formText = document.getElementById("formText");
const resetBtn = document.getElementById("resetBtn");
const bookItemRow = document.querySelector("#bookItem .row");

btn.addEventListener('click', async() => {
    // フォームに入力されたテキストの取得
    const textValue = formText.value;
    if (!textValue) return; // 入力がない場合は処理を中断

    // 書籍検索ができるGoogle Books APIのエンドポイントにフォームから取得したテキストを埋め込む
    const res = await fetch(`https://www.googleapis.com/books/v1/volumes?q=${textValue}`);
    const data = await res.json();

    // const bookItemRow = document.querySelector("#bookItem .row");
    bookItemRow.innerHTML = '';

    for(let i = 0; i < data.items.length; i++){
        // 例外が起きなければtryブロック内のコードが実行される
        try{
            // JSONデータの取得
            // 画像を表示するリンク
            const bookImg = data.items[i].volumeInfo.imageLinks.smallThumbnail;
            // 本のタイトル
            const bookTitle = data.items[i].volumeInfo.title;
            // 本の説明文
            const bookContent = data.items[i].volumeInfo.description;
            // 各書籍のGoogle Booksへのリンク
            const bookLink = data.items[i].volumeInfo.infoLink;
            
            const bookCard = `
                <div class="col">
                    <div class="card h-100">
                        <img src="${bookImg}" class="card-img-top" alt="${bookTitle}">
                        <div class="card-body">
                            <h5 class="card-title"><a href="${bookLink}" target="_blank">${bookTitle}</a></h5>
                            <p class="card-text">${bookContent}</p>
                        </div>
                    </div>
                </div>
            `;
            
            bookItemRow.insertAdjacentHTML('beforeend', bookCard);
        
        }catch(e){
            continue;
        };
    };

    // 検索後に入力フォームの値をクリアする
    formText.value = '';

  });

// リセットボタンのイベントリスナーを追加
resetBtn.addEventListener('click', () => {

    // 検索結果をクリア
    bookItemRow.innerHTML = '';

});