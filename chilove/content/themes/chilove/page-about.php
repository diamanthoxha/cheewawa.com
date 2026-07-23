<?php
get_header();
$totalPosts = count_posts();
$cats       = get_categories();
$editor     = chi_site_editor();
?>

<style>
.editor-card{display:flex;gap:18px;align-items:center;padding:22px;margin:1.4rem 0}
.editor-card img{border-radius:50%;flex-shrink:0}
.editor-card h2{margin:0 0 6px}
.editor-card .role{margin:0 0 8px;font-weight:700;color:var(--muted-cocoa)}
.editor-card p{margin:0}
@media (max-width:600px){.editor-card{flex-direction:column;text-align:center}}
</style>

<section class="page-head">
    <span class="eyebrow"><?= chi_paw(16) ?> About</span>
    <h1>Hi, we're <span class="accent">Cheewawa</span></h1>
    <p>Honest chihuahua care, training, and health guides — minus the myths.
       <?= (int) $totalPosts ?> articles and counting, all held to the same sourcing standard.</p>
</section>

<div class="about-prose" style="max-width:48rem;margin-inline:auto">

    <p>Cheewawa exists because the internet's chihuahua advice is a mess: recycled listicles,
       fabricated statistics, and myths that follow this breed everywhere. This site is the
       version we wished existed — practical guides grounded in what veterinary organizations
       and breed authorities actually say, written for people who take the world's smallest
       big personality seriously.</p>

    <?php if ($editor): ?>
    <div class="card editor-card">
        <?php if (!empty($editor->avatar)): ?>
            <img src="<?= esc_attr($editor->avatar) ?>" alt="<?= esc_attr($editor->display_name) ?>" width="96" height="96">
        <?php endif; ?>
        <div>
            <h2><a href="<?= esc_attr(author_permalink($editor)) ?>"><?= esc_html($editor->display_name) ?></a></h2>
            <p class="role">Founder &amp; Author</p>
            <p><?= esc_html($editor->bio) ?></p>
            <?php if ($sameas = chi_author_sameas($editor)): ?>
                <p style="margin-top:8px">
                <?php foreach ($sameas as $u): ?>
                    <a href="<?= esc_attr($u) ?>" rel="me noopener" target="_blank" aria-label="<?= esc_attr(parse_url($u, PHP_URL_HOST)) ?>"><?= str_contains($u, 'instagram') ? chi_icon('instagram', 20) : (str_contains($u, 'facebook') ? chi_icon('facebook', 20) : esc_html(parse_url($u, PHP_URL_HOST))) ?></a>
                <?php endforeach; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <h2>One author, many registers</h2>
    <p>Everything here is researched, written, and edited by <?= $editor ? esc_html($editor->display_name) : 'one person' ?> —
       and the writing deliberately changes register with the subject. Health guides read
       clinical and careful. Training guides are patient and step-by-step. The
       <a href="/category/stories">Stories</a> section runs lighter, sometimes tongue-in-cheek.
       Same author, same standards, different voice for different jobs.</p>

    <h2>How this site works</h2>
    <p><strong>Sources or it doesn't ship.</strong> Health and behavior claims are checked against
       primary organizations — veterinary manuals, the AKC and national breed clubs, the AVMA and
       ASPCA — and linked so you can read what she read. When the honest answer is
       "ask your own veterinarian," that's what the article says.</p>
    <p><strong>No fear, no fluff.</strong> No invented statistics, no panic-bait, no
       thousand-word intros. If a claim about this breed can't be traced to a real source,
       it gets debunked or left out.</p>
    <p><strong>Fun stays labeled.</strong> The <a href="/category/stories">Stories</a> section
       carries the lighter side — humor and fiction alongside real rescue features. Anything
       that isn't straight reporting is written so you can tell.</p>

    <h2>What we cover</h2>
    <div class="cover-list">
        <?php foreach ($cats as $c): ?>
            <a class="category-pill" href="<?= category_permalink($c) ?>"><?= esc_html($c->name) ?></a>
        <?php endforeach; ?>
    </div>

    <h2>Corrections</h2>
    <p>Spotted something wrong or out of date? <a href="/contact">Tell us</a> — corrections
       ship fast and we're glad to get them.</p>

    <div class="hero-cta">
        <a class="button-primary" href="/blog">Read the blog <?= chi_icon('arrow', 18) ?></a>
        <a class="button-secondary" href="/contact">Say hello</a>
    </div>
</div>

<?php get_footer(); ?>
