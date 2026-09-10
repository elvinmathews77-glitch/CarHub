<?php

header("Content-Type: image/svg+xml");

$car = isset($_GET["car"]) ? $_GET["car"] : "Car";

$car = htmlspecialchars($car, ENT_QUOTES, "UTF-8");

?>

<svg width="900" height="500" viewBox="0 0 900 500"
     xmlns="http://www.w3.org/2000/svg">

    <!-- Background -->
    <rect width="900" height="500" fill="#eef2f6"/>

    <!-- Ground -->
    <ellipse
        cx="450"
        cy="410"
        rx="300"
        ry="35"
        fill="#cbd5e1"
    />

    <!-- Car shadow -->
    <ellipse
        cx="450"
        cy="390"
        rx="260"
        ry="25"
        fill="#94a3b8"
        opacity="0.35"
    />

    <!-- Car body -->
    <path
        d="M180 350
           L215 290
           C230 265 260 250 300 245
           L355 170
           C365 155 380 145 405 145
           L550 145
           C575 145 590 155 605 175
           L665 245
           C705 250 735 270 750 300
           L775 350
           L775 370
           L170 370
           L170 350
           Z"
        fill="#e63946"
    />

    <!-- Roof -->
    <path
        d="M350 240
           L395 175
           L545 175
           L600 240
           Z"
        fill="#263244"
    />

    <!-- Front window -->
    <path
        d="M405 185
           L390 230
           L475 230
           L475 185
           Z"
        fill="#b9d7ea"
    />

    <!-- Rear window -->
    <path
        d="M490 185
           L490 230
           L580 230
           L545 185
           Z"
        fill="#b9d7ea"
    />

    <!-- Window shine -->
    <path
        d="M410 190 L400 215 L455 190 Z"
        fill="#ffffff"
        opacity="0.45"
    />

    <!-- Front bumper -->
    <rect
        x="715"
        y="330"
        width="60"
        height="35"
        rx="8"
        fill="#b91c1c"
    />

    <!-- Rear bumper -->
    <rect
        x="165"
        y="330"
        width="55"
        height="35"
        rx="8"
        fill="#b91c1c"
    />

    <!-- Headlight -->
    <rect
        x="700"
        y="295"
        width="55"
        height="25"
        rx="10"
        fill="#fff7c2"
    />

    <!-- Tail light -->
    <rect
        x="175"
        y="295"
        width="45"
        height="25"
        rx="10"
        fill="#7f1d1d"
    />

    <!-- Front wheel -->
    <circle
        cx="650"
        cy="365"
        r="48"
        fill="#111827"
    />

    <circle
        cx="650"
        cy="365"
        r="23"
        fill="#94a3b8"
    />

    <circle
        cx="650"
        cy="365"
        r="10"
        fill="#475569"
    />

    <!-- Rear wheel -->
    <circle
        cx="260"
        cy="365"
        r="48"
        fill="#111827"
    />

    <circle
        cx="260"
        cy="365"
        r="23"
        fill="#94a3b8"
    />

    <circle
        cx="260"
        cy="365"
        r="10"
        fill="#475569"
    />

    <!-- Door lines -->
    <line
        x1="470"
        y1="245"
        x2="470"
        y2="335"
        stroke="#b91c1c"
        stroke-width="3"
    />

    <line
        x1="575"
        y1="245"
        x2="575"
        y2="335"
        stroke="#b91c1c"
        stroke-width="3"
    />

    <!-- Door handles -->
    <rect
        x="500"
        y="265"
        width="35"
        height="6"
        rx="3"
        fill="#7f1d1d"
    />

    <rect
        x="600"
        y="265"
        width="35"
        height="6"
        rx="3"
        fill="#7f1d1d"
    />

    <!-- Car name -->
    <text
        x="450"
        y="455"
        text-anchor="middle"
        font-family="Arial, Helvetica, sans-serif"
        font-size="30"
        font-weight="bold"
        fill="#172033"
    >
        <?php echo $car; ?>
    </text>

</svg>