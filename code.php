<?php
session_start(); // Sessiyani boshlash

// --- SAVOLLAR VA JAVOBLAR BAZASI ---
// !!! DIQQAT !!! 'correct_answer' maydonini har bir savol uchun TO'G'RILAB CHIISHINGIZ KERAK!
$questionsData = [
    // Barcha savollarni shu yerga avvalgi javobdagi formatda PHP massivi sifatida kiriting
    // Misol:
    [
        'id' => 1,
        'question' => "1. Montaj massasi bu-", // Keyinroq raqamni olib tashlaymiz
        'options' => [
            "bu montaj qilinayotgan konstruksiya va u bilan birgalikda ko‘tarilayotgan moslamalarining massasi.",
            "bu montaj qilinayotgan konstruksiyaning massasi",
            "ko’taruchi qurilmaning massasi"
        ],
        'correct_answer' => "bu montaj qilinayotgan konstruksiya va u bilan birgalikda ko‘tarilayotgan moslamalarining massasi.",
        'book' => "Qurilishni tashkil etish va rejalashtirish (Toshkent – 2022)",
        'page' => 103
    ],
    [   'id' => 2,
        'question' => "2. Montaj balandligi bu-",
        'options' => ["konstruksiyalarni o‘rnatish balandligi (sathiy belgisi), yer sathidan yoki montaj qilinayotgan elementlarining tayanch yuzasidan (tayanchdan konstruksiyalarni ko‘tarish balandligi) zahira balandligi, montaj qilinayotgan konstruksiyalarning balandligi (uzunligi va qalinligi), stroplar balandligi yoki yukni qamrab (ushlab) oluvchi moslamalarining balandliklari yig‘indisi","konstruksiyalarni o‘rnatish balandligi (sathiy belgisi), yer sathidan yoki montaj qilinayotgan elementlarining tayanch yuzasidan (tayanchdan konstruksiyalarni ko‘tarish balandligi) zahira balandligi, montaj qilinayotgan konstruksiyalarning balandligi (uzunligi va qalinligi) yig‘indisi","konstruksiyalarni o‘rnatish balandligi (sathiy belgisi montaj qilinayotgan konstruksiyalarning balandligi (uzunligi va qalinligi), stroplar balandligi yoki yukni qamrab (ushlab) oluvchi moslamalarining balandliklari yig‘indisi"],
        'correct_answer' => "konstruksiyalarni o‘rnatish balandligi (sathiy belgisi), yer sathidan yoki montaj qilinayotgan elementlarining tayanch yuzasidan (tayanchdan konstruksiyalarni ko‘tarish balandligi) zahira balandligi, montaj qilinayotgan konstruksiyalarning balandligi (uzunligi va qalinligi), stroplar balandligi yoki yukni qamrab (ushlab) oluvchi moslamalarining balandliklari yig‘indisi",'book': "Toshkent-2022",'page': 104 ],
    [   'id' => 3,
        'question' => "3. H= h0 + hzah + h + hstr qaysi turdagi kranning ilmog’ini uzunligini hisoblash formulasi",
        'options' => ["minor tipidagi kran ilmog’ining ko‘tarilish balandligi","Avto kran ilmog’ining ko‘tarilish balandligi","Sepli kran ilmog’ining ko‘tarilish balandligi"],
        'correct_answer' => "minor tipidagi kran ilmog’ining ko‘tarilish balandligi",'book': "Toshkent-2022",'page': 104 ],
    // ... AVVALGI JAVOBDAGI BARCHA SAVOLLARNI SHU YERGA PHP MASSIVI SIFATIDA KIRITING ...
    [   'id' => 191,'question' => "191. Qurilish ishlab chiqarishini tashkil qilishning necha xil usuli mavjud",
        'options' => [ "3ta", "4 ta", "2 ta", "5 ta" ],
        'correct_answer' => "3ta", # VAQTINCHA
        'book': "Test Sheet Image 27",'page': None],

];

// --- Funksiyalar ---
function shuffle_assoc(&$array) {
    $keys = array_keys($array);
    shuffle($keys);
    $new = [];
    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }
    $array = $new;
    return true;
}

function get_question_by_id($id, $questions) {
    foreach ($questions as $q) {
        if ($q['id'] == $id) {
            return $q;
        }
    }
    return null;
}

// --- Sessiya va O'yin Mantiqi ---
$batch_size = 25;
$total_questions = count($questionsData);
$num_batches = ceil($total_questions / $batch_size);

// O'yin holatini boshqarish
if (!isset($_SESSION['quiz_state'])) {
    $_SESSION['quiz_state'] = 'start';
}

// POST so'rovini qayta ishlash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'none';

    if ($action === 'start_quiz') {
        $_SESSION['questions_data'] = $questionsData; // Savollarni sessiyaga saqlash
        shuffle($_SESSION['questions_data']); // Barcha savollarni bir marta aralashtirish
        $_SESSION['overall_score'] = 0;
        $_SESSION['current_batch_index'] = 0;
        $_SESSION['quiz_state'] = 'start_batch'; // Yangi holat: qismni boshlash
    }
    elseif ($action === 'submit_answer') {
        $question_id = $_POST['question_id'] ?? null;
        $submitted_answer = $_POST['answer'] ?? null;
        $current_q_data = get_question_by_id($question_id, $_SESSION['questions_data']); // Asl data'dan olish

        if ($current_q_data && $submitted_answer !== null) {
            $is_correct = ($submitted_answer === $current_q_data['correct_answer']);
            $_SESSION['last_answer_correct'] = $is_correct;
            $_SESSION['last_correct_answer_text'] = $current_q_data['correct_answer'];
            $_SESSION['last_book'] = $current_q_data['book'] ?? 'Noma\'lum';
            $_SESSION['last_page'] = $current_q_data['page'] ?? 'Noma\'lum';

            if ($is_correct) {
                $_SESSION['batch_score']++;
            } else {
                $_SESSION['batch_incorrect_ids'][] = $question_id;
            }
            $_SESSION['current_question_index_in_batch']++;
            $_SESSION['quiz_state'] = 'show_feedback'; // Javobni ko'rsatish holati
        }
    }
    elseif ($action === 'next_question') {
         // Agar qism tugagan bo'lsa, natijalarni ko'rsatish
        if ($_SESSION['current_question_index_in_batch'] >= count($_SESSION['current_batch_questions'])) {
            $_SESSION['quiz_state'] = 'batch_results';
            $_SESSION['overall_score'] += $_SESSION['batch_score']; // Umumiy balni yangilash
        } else {
            $_SESSION['quiz_state'] = 'batch_questions'; // Keyingi savolga o'tish
        }
    }
     elseif ($action === 'start_retry') {
         $incorrect_ids = $_SESSION['batch_incorrect_ids'] ?? [];
         $_SESSION['questions_to_retry'] = [];
         if (!empty($incorrect_ids)) {
             foreach ($_SESSION['questions_data'] as $q) { // Asl data'dan qidirish
                 if (in_array($q['id'], $incorrect_ids)) {
                     $_SESSION['questions_to_retry'][] = $q;
                 }
             }
             shuffle($_SESSION['questions_to_retry']); // Xato savollarni aralashtirish
         }
         $_SESSION['current_retry_index'] = 0;
         $_SESSION['quiz_state'] = 'retry_questions';
     }
     elseif ($action === 'submit_retry_answer') {
         $question_id = $_POST['question_id'] ?? null;
         $submitted_answer = $_POST['answer'] ?? null;
         $current_q_data = get_question_by_id($question_id, $_SESSION['questions_to_retry']);

         if ($current_q_data && $submitted_answer !== null) {
             $_SESSION['last_answer_correct'] = ($submitted_answer === $current_q_data['correct_answer']);
             $_SESSION['last_correct_answer_text'] = $current_q_data['correct_answer'];
             $_SESSION['last_book'] = $current_q_data['book'] ?? 'Noma\'lum';
             $_SESSION['last_page'] = $current_q_data['page'] ?? 'Noma\'lum';
             $_SESSION['last_retry_question_id'] = $question_id; // Qaysi savolga javob berilganini bilish uchun
         }
         $_SESSION['current_retry_index']++;
         $_SESSION['quiz_state'] = 'show_retry_feedback';
     }
     elseif ($action === 'next_retry_question') {
          if ($_SESSION['current_retry_index'] >= count($_SESSION['questions_to_retry'])) {
              // Agar xatolar tugasa, keyingi qismga o'tish
              $_SESSION['current_batch_index']++;
              if ($_SESSION['current_batch_index'] >= $num_batches) {
                  $_SESSION['quiz_state'] = 'final_results';
              } else {
                  $_SESSION['quiz_state'] = 'start_batch';
              }
          } else {
              $_SESSION['quiz_state'] = 'retry_questions';
          }
     }
    elseif ($action === 'next_batch') {
        $_SESSION['current_batch_index']++;
        if ($_SESSION['current_batch_index'] >= $num_batches) {
            $_SESSION['quiz_state'] = 'final_results';
        } else {
            $_SESSION['quiz_state'] = 'start_batch'; // Keyingi qismni boshlash
        }
    }
     elseif ($action === 'restart_quiz') {
         session_unset(); // Sessiyani tozalash
         session_destroy();
         session_start(); // Yangi sessiya boshlash
         $_SESSION['quiz_state'] = 'start';
         header("Location: " . $_SERVER['PHP_SELF']); // Sahifani yangilash
         exit;
     }
}

// Qismni boshlash logikasi
if ($_SESSION['quiz_state'] === 'start_batch') {
    $start_index = $_SESSION['current_batch_index'] * $batch_size;
    $end_index = min($start_index + $batch_size, $total_questions);
    // Sessiyadagi aralashtirilgan savollardan qismni olish
    $_SESSION['current_batch_questions'] = array_slice($_SESSION['questions_data'], $start_index, $batch_size);
    // Qism ichidagi savollarni qayta aralashtirish
    shuffle($_SESSION['current_batch_questions']);

    $_SESSION['current_question_index_in_batch'] = 0;
    $_SESSION['batch_score'] = 0;
    $_SESSION['batch_incorrect_ids'] = [];
    $_SESSION['questions_to_retry'] = [];
    $_SESSION['current_retry_index'] = 0;
    $_SESSION['quiz_state'] = 'batch_questions'; // Savollarni ko'rsatishga o'tish
}

?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qurilish Testi (PHP)</title>
    <style>
        /* CSS stillari (avvalgi HTML javobdagidek) */
        body { font-family: sans-serif; line-height: 1.6; margin: 20px; background-color: #f4f4f4; }
        .quiz-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); max-width: 800px; margin: 30px auto; }
        h1, h3 { text-align: center; color: #333; }
        #question-text, #retry-question-text { font-size: 1.25em; margin-bottom: 25px; font-weight: bold; color: #555; }
        .options-container button, #retry-options-container button { display: block; width: 100%; padding: 12px 15px; margin: 10px 0; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1.05em; text-align: left; transition: background-color 0.2s ease, transform 0.1s ease; }
        .options-container button:hover:not(:disabled), #retry-options-container button:hover:not(:disabled) { background-color: #0056b3; transform: translateY(-2px); }
        .options-container button:active:not(:disabled), #retry-options-container button:active:not(:disabled) { transform: translateY(0px); }
        .options-container button:disabled, #retry-options-container button:disabled { cursor: not-allowed; opacity: 0.6; }
        #feedback, #retry-feedback { margin-top: 20px; padding: 12px; border-radius: 5px; font-weight: bold; font-size: 1.1em; }
        .feedback-correct { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .feedback-incorrect { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .source-info { font-size: 0.85em; color: #555; margin-top: 8px; display: block; }
        button[type="submit"], .action-button { padding: 12px 25px; font-size: 1em; cursor: pointer; border-radius: 5px; border: none; margin-top: 25px; margin-right: 10px; background-color: #17a2b8; color: white; transition: background-color 0.3s ease; }
        button[name="action"][value="next_question"], button[name="action"][value="next_retry_question"], button[name="action"][value="next_batch"] { background-color: #28a745; }
        button[name="action"][value="start_retry"] { background-color: #ffc107; color: black; }
        button[name="action"][value="next_batch"] { background-color: #6c757d; }
        button[type="submit"]:hover, .action-button:hover { opacity: 0.9; }
        .hidden { display: none; }
        #incorrect-list { list-style-type: none; padding-left: 0; }
        #incorrect-list li { margin-bottom: 8px; color: #721c24; font-size: 0.95em; border-bottom: 1px dotted #ddd; padding-bottom: 5px;}
        #incorrect-list strong { color: #5a161c;}
        #progress-indicator { margin-bottom: 20px; font-weight: bold; color: #495057; text-align: right; font-size: 0.9em; }
        #batch-results, #final-results { margin-top: 30px; padding: 20px; border: 1px solid #dee2e6; background-color: #e9ecef; border-radius: 5px; }
        #batch-results h3, #final-results h3 { margin-top: 0; color: #343a40; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 15px; }
        #retry-area h3 { margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="quiz-container">
    <h1>Qurilish Bo'yicha Test (PHP)</h1>

    <?php // --- PHP yordamida HTML generatsiyasi ---

    // Boshlang'ich ekran
    if ($_SESSION['quiz_state'] === 'start'):
    ?>
        <div id="start-screen">
             <p>Testni boshlash uchun quyidagi tugmani bosing. Test qismlarga bo'lingan va har bir qismda 25 tagacha savol mavjud. Variantlar va savollar ketma-ketligi aralashtiriladi.</p>
             <form method="post">
                 <input type="hidden" name="action" value="start_quiz">
                 <button type="submit">Testni Boshlash</button>
             </form>
        </div>

    <?php
    // Savol ko'rsatish
    elseif ($_SESSION['quiz_state'] === 'batch_questions'):
        $q_index = $_SESSION['current_question_index_in_batch'];
        if (isset($_SESSION['current_batch_questions'][$q_index])) {
            $current_q = $_SESSION['current_batch_questions'][$q_index];
            // Savol raqamini olib tashlash
            $question_text_only = preg_replace('/^\d+\.\s*/', '', $current_q['question']);
            $options = $current_q['options'];
            shuffle($options); // Variantlarni aralashtirish
    ?>
        <div id="quiz-area">
            <div id="progress-indicator">
                Qism <?php echo $_SESSION['current_batch_index'] + 1; ?> / <?php echo $num_batches; ?> |
                Savol <?php echo $q_index + 1; ?> / <?php echo count($_SESSION['current_batch_questions']); ?>
            </div>
            <div id="question-text"><?php echo htmlspecialchars($question_text_only); ?></div>
            <form method="post" class="options-container">
                <input type="hidden" name="action" value="submit_answer">
                <input type="hidden" name="question_id" value="<?php echo $current_q['id']; ?>">
                <?php foreach ($options as $option): ?>
                    <button type="submit" name="answer" value="<?php echo htmlspecialchars($option); ?>">
                        <?php echo htmlspecialchars($option); ?>
                    </button>
                <?php endforeach; ?>
            </form>
        </div>
    <?php
        } else {
             // Bu holat bo'lmasligi kerak, lekin xavfsizlik uchun
             $_SESSION['quiz_state'] = 'batch_results';
             header("Location: " . $_SERVER['PHP_SELF']);
             exit;
        }

    // Javob natijasini ko'rsatish
    elseif ($_SESSION['quiz_state'] === 'show_feedback'):
    ?>
        <div id="feedback" class="<?php echo $_SESSION['last_answer_correct'] ? 'feedback-correct' : 'feedback-incorrect'; ?>">
            <?php if ($_SESSION['last_answer_correct']): ?>
                ✅ To'g'ri!
            <?php else: ?>
                ❌ Noto'g'ri! To'g'ri javob: <?php echo htmlspecialchars($_SESSION['last_correct_answer_text']); ?>
                <div class="source-info">
                    Manba: <?php echo htmlspecialchars($_SESSION['last_book']); ?>,
                    Sahifa: <?php echo htmlspecialchars($_SESSION['last_page']); ?>
                </div>
            <?php endif; ?>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="next_question">
            <button type="submit" class="action-button">
                <?php echo ($_SESSION['current_question_index_in_batch'] >= count($_SESSION['current_batch_questions'])) ? "Qism Natijalarini Ko'rish" : "Keyingi Savol"; ?>
            </button>
        </form>

    <?php
    // Qism natijalarini ko'rsatish
    elseif ($_SESSION['quiz_state'] === 'batch_results'):
        $batch_q_count = count($_SESSION['current_batch_questions']);
        $batch_s = $_SESSION['batch_score'];
        $incorrect_ids = $_SESSION['batch_incorrect_ids'] ?? [];
        $current_batch_idx = $_SESSION['current_batch_index'];
    ?>
        <div id="batch-results">
            <h3><?php echo $current_batch_idx + 1; ?>-Qism Natijasi</h3>
            <p>Siz bu qismdagi <?php echo $batch_q_count; ?> ta savoldan <?php echo $batch_s; ?> tasiga to'g'ri javob berdingiz.</p>
            <?php if (!empty($incorrect_ids)): ?>
                <h4>Xato qilingan savollar (IDlari):</h4>
                <ul id="incorrect-list">
                    <?php
                     // Xato savollarni ID bo'yicha tartiblash
                     sort($incorrect_ids);
                     foreach ($incorrect_ids as $id):
                         $q_info = get_question_by_id($id, $_SESSION['questions_data']); // Get full info
                         if($q_info) {
                              // Savol raqamini olib tashlash
                             $q_text_only = preg_replace('/^\d+\.\s*/', '', $q_info['question']);
                             echo '<li><strong>ID ' . htmlspecialchars($id) . ':</strong> ' . htmlspecialchars($q_text_only) . '</li>';
                         }
                     endforeach;
                     ?>
                </ul>
                <form method="post" style="display: inline-block;">
                    <input type="hidden" name="action" value="start_retry">
                    <button type="submit" class="action-button">Xatolarni Qayta Ishlash</button>
                </form>
                <form method="post" style="display: inline-block;">
                     <input type="hidden" name="action" value="next_batch">
                     <button type="submit" class="action-button">Yo'q, Keyingi Qism</button>
                </form>
            <?php else: ?>
                <p>Bu qismda xato qilmadingiz!</p>
                <form method="post">
                    <input type="hidden" name="action" value="next_batch">
                    <button type="submit" class="action-button">
                        <?php echo ($current_batch_idx >= $num_batches - 1) ? "Yakuniy Natijalar" : "Keyingi Qism"; ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>

     <?php
     // Xatolarni qayta ishlash savolini ko'rsatish
     elseif ($_SESSION['quiz_state'] === 'retry_questions'):
         $r_index = $_SESSION['current_retry_index'];
         if (isset($_SESSION['questions_to_retry'][$r_index])) {
             $current_q = $_SESSION['questions_to_retry'][$r_index];
             $question_text_only = preg_replace('/^\d+\.\s*/', '', $current_q['question']);
             $options = $current_q['options'];
             shuffle($options);
     ?>
         <div id="retry-area">
             <h3>Xatolar Ustida Ishlash</h3>
             <div id="retry-progress-indicator">
                 Xato savol <?php echo $r_index + 1; ?> / <?php echo count($_SESSION['questions_to_retry']); ?>
             </div>
             <div id="retry-question-text"><b>ID <?php echo $current_q['id']; ?> (Qayta):</b> <?php echo htmlspecialchars($question_text_only); ?></div>
             <form method="post" id="retry-options-container">
                 <input type="hidden" name="action" value="submit_retry_answer">
                 <input type="hidden" name="question_id" value="<?php echo $current_q['id']; ?>">
                 <?php foreach ($options as $option): ?>
                     <button type="submit" name="answer" value="<?php echo htmlspecialchars($option); ?>">
                         <?php echo htmlspecialchars($option); ?>
                     </button>
                 <?php endforeach; ?>
             </form>
         </div>
     <?php
         } else {
             // Bu holat bo'lmasligi kerak, xatolar tugagan bo'lsa
             $_SESSION['current_batch_index']++;
              if ($_SESSION['current_batch_index'] >= $num_batches) {
                  $_SESSION['quiz_state'] = 'final_results';
              } else {
                  $_SESSION['quiz_state'] = 'start_batch';
              }
             header("Location: " . $_SERVER['PHP_SELF']);
             exit;
         }

     // Xatoga javob natijasini ko'rsatish
     elseif ($_SESSION['quiz_state'] === 'show_retry_feedback'):
          $last_retry_id = $_SESSION['last_retry_question_id'] ?? null;
          $retry_q_data = get_question_by_id($last_retry_id, $_SESSION['questions_to_retry']);
          $question_text_only = $retry_q_data ? preg_replace('/^\d+\.\s*/', '', $retry_q_data['question']) : '';
     ?>
        <div id="retry-area">
            <h3>Xatolar Ustida Ishlash</h3>
             <div id="retry-progress-indicator">
                 Xato savol <?php echo $_SESSION['current_retry_index']; ?> / <?php echo count($_SESSION['questions_to_retry']); ?> (Javob berildi)
             </div>
             <?php if($retry_q_data): ?>
                 <div id="retry-question-text"><b>ID <?php echo $retry_q_data['id']; ?> (Qayta):</b> <?php echo htmlspecialchars($question_text_only); ?></div>
             <?php endif; ?>
             <div id="retry-feedback" class="<?php echo $_SESSION['last_answer_correct'] ? 'feedback-correct' : 'feedback-incorrect'; ?>">
                 <?php if ($_SESSION['last_answer_correct']): ?>
                     ✅ To'g'ri!
                 <?php else: ?>
                     ❌ Noto'g'ri! To'g'ri javob: <?php echo htmlspecialchars($_SESSION['last_correct_answer_text']); ?>
                     <div class="source-info">
                         Manba: <?php echo htmlspecialchars($_SESSION['last_book']); ?>,
                         Sahifa: <?php echo htmlspecialchars($_SESSION['last_page']); ?>
                     </div>
                 <?php endif; ?>
             </div>
             <form method="post">
                 <input type="hidden" name="action" value="next_retry_question">
                 <button type="submit" class="action-button">
                     <?php echo ($_SESSION['current_retry_index'] >= count($_SESSION['questions_to_retry'])) ? "Keyingi Qism / Natijalar" : "Keyingi Xato Savol"; ?>
                 </button>
             </form>
        </div>

    <?php
    // Yakuniy natijalar
    elseif ($_SESSION['quiz_state'] === 'final_results'):
        $final_score = $_SESSION['overall_score'] ?? 0;
        $percentage = ($total_questions > 0) ? round(($final_score / $total_questions) * 100, 2) : 0;
    ?>
        <div id="final-results">
            <h3>Yakuniy Natija</h3>
            <p>Siz jami <?php echo $total_questions; ?> ta savoldan <?php echo $final_score; ?> tasiga to'g'ri javob berdingiz. (<?php echo $percentage; ?>%)</p>
            <form method="post">
                <input type="hidden" name="action" value="restart_quiz">
                <button type="submit" class="action-button">Testni Qayta Boshlash</button>
            </form>
        </div>

    <?php endif; ?>

</div>

</body>
</html>