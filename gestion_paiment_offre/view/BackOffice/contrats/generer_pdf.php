<?php
/**
 * view/BackOffice/contrats/generer_pdf.php
 * Génération du contrat PDF — BackOffice Protex 2026
 */

if (!defined('BASE_URL')) define('BASE_URL', '/projet_web1/gestion_paiment_offre');
$base = '/projet_web1/gestion_paiment_offre';

function cE($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function dFmt($v): string { return number_format((float)($v ?? 0), 3, '.', ' ') . ' DT'; }
function dDate($d): string {
    if (!$d) return '—';
    try { return date('d/m/Y', strtotime($d)); } catch (Exception $e) { return cE($d); }
}

$ref = $contrat['numero_contrat'] ?? '';
$client = ($contrat['prenom'] ?? '') . ' ' . ($contrat['nom'] ?? '');
$typeAssurance = $contrat['type_assurance'] ?? '';
$offre = $contrat['nom_offre'] ?? '';
$montant = $contrat['prime_contrat'] ?? 0;
$periodicite = $contrat['type_contrat'] ?? 'mensuel';
$dateDebut = $contrat['date_debut_contrat'] ?? date('Y-m-d');
$dateFin = $contrat['date_fin_contrat'] ?? date('Y-m-d', strtotime('+1 year'));
$franchise = $contrat['franchise_contrat'] ?? 0;
$couverture = $contrat['couverture'] ?? '';

$typeLabels = ['auto' => 'Automobile', 'habitation' => 'Habitation', 'sante' => 'Santé'];
$typeLabel = $typeLabels[$typeAssurance] ?? 'Assurance';
$periodiciteLabel = $periodicite === 'annuel' ? 'annuelle' : 'mensuelle';

$today = date('d/m/Y');
$todayLong = date('d F Y');
$mois = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$m = (int)date('n');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat <?= cE($ref) ?> — <?= cE($typeLabel) ?> — PROTEX Assurance</title>
    <style>
        @page { size: A4; margin: 20mm 18mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #1a1a1a; line-height: 1.55; }

        .page-break { page-break-after: always; }

        .header { text-align: center; border-bottom: 3px solid #1A3A7A; padding-bottom: 16px; margin-bottom: 24px; }
        .header-logo { font-size: 26pt; font-weight: 900; color: #1A3A7A; letter-spacing: 2px; }
        .header-logo span { color: #FF6B1A; }
        .header-sub { font-size: 9pt; color: #666; margin-top: 2px; text-transform: uppercase; letter-spacing: 3px; }
        .header-addr { font-size: 8.5pt; color: #888; margin-top: 6px; }

        .contract-title { text-align: center; margin: 28px 0 20px; }
        .contract-title h1 { font-size: 16pt; color: #1A3A7A; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
        .contract-title h2 { font-size: 12pt; color: #FF6B1A; font-weight: 700; margin-top: 4px; }
        .contract-ref { text-align: right; font-size: 9pt; color: #666; margin-bottom: 20px; font-family: monospace; }

        .section { margin-bottom: 18px; }
        .section-title { font-size: 12pt; font-weight: 700; color: #1A3A7A; border-bottom: 1px solid #ddd; padding-bottom: 6px; margin-bottom: 12px; }
        .section-title .num { color: #FF6B1A; margin-right: 6px; }

        .info-grid { display: flex; gap: 20px; margin-bottom: 16px; }
        .info-col { flex: 1; }
        .info-label { font-size: 8pt; text-transform: uppercase; color: #888; letter-spacing: .5px; font-weight: 700; }
        .info-value { font-size: 11pt; color: #1a1a1a; font-weight: 600; margin-top: 2px; }

        .table-ctr { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 10pt; }
        .table-ctr th { background: #1A3A7A; color: #fff; padding: 8px 10px; text-align: left; font-size: 9pt; text-transform: uppercase; letter-spacing: .5px; }
        .table-ctr td { padding: 7px 10px; border-bottom: 1px solid #e0e0e0; }
        .table-ctr tr:nth-child(even) { background: #f8f9fa; }

        .clause { margin-bottom: 10px; }
        .clause-title { font-size: 10.5pt; font-weight: 700; color: #333; margin-bottom: 4px; }
        .clause p { text-align: justify; margin-bottom: 6px; }

        .highlight-box { background: #f0f4ff; border-left: 4px solid #1A3A7A; padding: 12px 16px; margin: 14px 0; border-radius: 0 6px 6px 0; }
        .highlight-box.orange { background: #fff8f0; border-left-color: #FF6B1A; }
        .highlight-box.green { background: #f0fff4; border-left-color: #28a745; }

        .signature-section { margin-top: 40px; page-break-inside: avoid; }
        .signature-grid { display: flex; gap: 40px; margin-top: 30px; }
        .signature-col { flex: 1; }
        .signature-label { font-size: 9pt; font-weight: 700; color: #666; text-transform: uppercase; margin-bottom: 50px; }
        .signature-line { border-top: 1px solid #333; padding-top: 6px; font-size: 8.5pt; color: #888; text-align: center; }

        .footer { text-align: center; margin-top: 30px; padding-top: 12px; border-top: 1px solid #ddd; font-size: 8pt; color: #999; }

        ul.ctr-list { margin-left: 20px; margin-bottom: 8px; }
        ul.ctr-list li { margin-bottom: 3px; }

        .mention-legale { font-size: 8pt; color: #888; text-align: justify; margin-top: 20px; padding: 10px; background: #f5f5f5; border-radius: 4px; }

        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="no-print" style="position:fixed;top:12px;right:16px;z-index:999;display:flex;gap:8px;">
    <button onclick="window.print()" style="padding:10px 20px;border-radius:8px;border:none;background:#1A3A7A;color:#fff;font-size:13px;font-weight:700;cursor:pointer;">
        🖨️ Imprimer / Sauvegarder PDF
    </button>
    <a href="<?= $base ?>/controller/ContratController.php?action=details&id=<?= (int)$contrat['id_contrat'] ?>" style="padding:10px 20px;border-radius:8px;border:1px solid #ddd;background:#fff;color:#333;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;">
        ← Retour
    </a>
</div>

<div style="padding:20px;">

    <div class="header">
        <div class="header-logo">PROTEX <span>Assurance</span></div>
        <div class="header-sub">Société d'assurance tunisienne agréée</div>
        <div class="header-addr">Siège social : Les Berges du Lac, 1053 Tunis, Tunisie — Tél : +216 71 234 567 — Email : contact@protex-assurance.tn</div>
    </div>

    <div class="contract-title">
        <h1>Contrat d'Assurance</h1>
        <h2><?= cE($typeLabel) ?></h2>
    </div>

    <div class="contract-ref">
        Contrat n° <?= cE($ref) ?> — Établi le <?= $today ?>
    </div>

    <div class="section">
        <div class="section-title"><span class="num">I.</span> Parties au contrat</div>
        <div class="info-grid">
            <div class="info-col">
                <div class="info-label">L'assureur</div>
                <div class="info-value">PROTEX Assurances S.A.</div>
                <div style="font-size:9pt;color:#666;margin-top:4px;">
                    Registre de commerce : B 25689 2010<br>
                    N° agrément : MF 0814567V<br>
                    Siège social : Les Berges du Lac, Tunis
                </div>
            </div>
            <div class="info-col">
                <div class="info-label">L'assuré</div>
                <div class="info-value"><?= cE($client) ?></div>
                <div style="font-size:9pt;color:#666;margin-top:4px;">
                    Email : <?= cE($contrat['email'] ?? '—') ?><br>
                    Téléphone : <?= cE($contrat['telephone'] ?? '—') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title"><span class="num">II.</span> Objet du contrat</div>
        <p>Le présent contrat d'assurance <?= strtolower(cE($typeLabel)) ?> est conclu entre la société <strong>PROTEX Assurances S.A.</strong>, ci-après désignée « l'Assureur », et <strong><?= cE($client) ?></strong>, ci-après désigné « l'Assuré », aux conditions générales et particulières définies ci-dessous.</p>
        <p>Ce contrat a pour objet la couverture des risques spécifiés dans les conditions particulières, conformément aux dispositions du Code des Assurances tunisien et à la réglementation en vigueur.</p>
    </div>

    <div class="section">
        <div class="section-title"><span class="num">III.</span> Conditions particulières</div>
        <table class="table-ctr">
            <thead>
                <tr><th>Rubrique</th><th>Valeur</th></tr>
            </thead>
            <tbody>
                <tr><td><strong>Numéro de contrat</strong></td><td><?= cE($ref) ?></td></tr>
                <tr><td><strong>Type d'assurance</strong></td><td><?= cE($typeLabel) ?></td></tr>
                <tr><td><strong>Offre souscrite</strong></td><td><?= cE($offre) ?></td></tr>
                <tr><td><strong>Prime <?= cE($periodiciteLabel) ?></strong></td><td style="font-weight:700;"><?= dFmt($montant) ?></td></tr>
                <tr><td><strong>Franchise</strong></td><td><?= dFmt($franchise) ?></td></tr>
                <tr><td><strong>Date d'effet</strong></td><td><?= dDate($dateDebut) ?></td></tr>
                <tr><td><strong>Date d'échéance</strong></td><td><?= dDate($dateFin) ?></td></tr>
                <?php if (!empty($couverture)): ?>
                <tr><td><strong>Éléments de couverture</strong></td><td><?= cE($couverture) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title"><span class="num">IV.</span> Conditions générales</div>

        <div class="clause">
            <div class="clause-title">Article 1 — Déclarations de l'assuré</div>
            <p>L'assuré déclare, au moment de la souscription du présent contrat, que les informations fournies sont exactes et complètes. Toute réticence, toute déclaration fausse ou inexacte, toute omission susceptible de modifier l'opinion du risque peut entraîner la nullité du contrat conformément à l'article 44 du Code des Assurances.</p>
            <p>L'assuré s'engage à déclarer dans les quinze (15) jours toute aggravation du risque et toute modification de sa situation personnelle ou professionnelle susceptible d'entraîner un changement dans la tarification ou les conditions du contrat.</p>
        </div>

        <div class="clause">
            <div class="clause-title">Article 2 — Prise d'effet et durée du contrat</div>
            <p>Le présent contrat prend effet à compter du <strong><?= dDate($dateDebut) ?></strong> et expire le <strong><?= dDate($dateFin) ?></strong>, sauf renouvellement, résiliation ou modification anticipée conformément aux conditions prévues aux articles suivants.</p>
            <p>Le contrat est renouvelable par tacite reconduction pour des périodes successives de <?= cE($periodiciteLabel === 'mensuelle' ? 'un (1) mois' : 'un (1) an') ?>, sauf dénonciation par l'une des parties par lettre recommandée avec accusé de réception, moyennant un préavis de trente (30) jours avant l'échéance.</p>
        </div>

        <div class="clause">
            <div class="clause-title">Article 3 — Prime et modalités de paiement</div>
            <p>La prime <?= strtolower(cE($periodiciteLabel)) ?> due par l'assuré s'élève à <strong><?= dFmt($montant) ?></strong> (<?= cE($montant) ?> Dinars Tunisiens).</p>
            <p>Le paiement de la prime est effectué par <?= cE($periodicite === 'annuel' ? 'versement annuel' : 'versement mensuel') ?>. En cas de non-paiement de la prime à l'échéance convenue, la garantie sera suspendue trente (30) jours après un mise en demeure restée infructueuse, conformément à l'article 63 du Code des Assurances.</p>

            <div class="highlight-box">
                <strong>Important :</strong> L'assuré est informé que le non-paiement de la prime dans le délai imparti peut entraîner la résiliation du contrat par l'assureur, sans préjudice du paiement des primes échues.
            </div>
        </div>

        <div class="clause">
            <div class="clause-title">Article 4 — Franchise</div>
            <p>En cas de sinistre, la franchise de <strong><?= dFmt($franchise) ?></strong> reste à la charge exclusive de l'assuré. L'indemnisation par l'assureur ne portera que sur la part du dommage excédant le montant de la franchise, dans la limite des plafonds de garantie stipulés au contrat.</p>
        </div>

        <div class="clause">
            <div class="clause-title">Article 5 — Obligations de l'assuré en cas de sinistre</div>
            <p>En cas de sinistre couvert par le présent contrat, l'assuré est tenu de :</p>
            <ul class="ctr-list">
                <li>Notifier le sinistre à l'assureur dans un délai maximum de <strong>cinq (5) jours ouvrables</strong> à compter de sa survenance, par tout moyen écrit ;</li>
                <li>Fournir tous les documents et informations nécessaires à l'établissement du sinistre (procès-verbal, factures, photographies, témoignages, etc.) ;</li>
                <li>Prendre les mesures conservatoires raisonnables pour limiter les conséquences du sinistre ;</li>
                <li>Ne reconnaître aucune responsabilité ni accepter aucune indemnité sans l'accord préalable de l'assureur ;</li>
                <li>Coopérer pleinement avec l'assureur et ses experts dans l'évaluation et le règlement du sinistre.</li>
            </ul>
        </div>

        <div class="clause">
            <div class="clause-title">Article 6 — Garanties et exclusions</div>
            <p>L'assureur garantit les dommages et pertes spécifiés dans les conditions particulières, dans la limite des plafonds de couverture convenus.</p>
            <p>Sont exclus de la garantie, sauf convention contraire expresse :</p>
            <ul class="ctr-list">
                <li>Les dommages résultant d'un fait intentionnel ou dolosif de l'assuré ;</li>
                <li>Les dommages résultant de la guerre civile, émeutes ou mouvements populaires ;</li>
                <li>Les catastrophes naturelles, sauf extension de garantie prévue au contrat ;</li>
                <li>Les dommages résultant de l'inexécution des obligations prévues au présent contrat ;</li>
                <li>Les pertes indirectes, le manque à gagner et les dommages immatériels.</li>
            </ul>
        </div>

        <div class="page-break"></div>

        <div class="section">
            <div class="section-title"><span class="num">V.</span> Conditions particulières — <?= cE($typeLabel) ?></div>

            <?php if ($typeAssurance === 'auto'): ?>
                <div class="clause">
                    <div class="clause-title">Article 7.1 — Véhicule assuré</div>
                    <p>Le présent contrat couvre le véhicule automobile désigné par l'assuré lors de la souscription, conformément aux informations fournies dans le formulaire de devis. Les garanties s'appliquent au véhicule décrit ainsi qu'à ses équipements standards.</p>
                </div>
                <div class="clause">
                    <div class="clause-title">Article 7.2 — Garanties automobile</div>
                    <p>L'offre <strong><?= cE($offre) ?></strong> comprend les garanties suivantes :</p>
                    <ul class="ctr-list">
                        <li><strong>Responsabilité civile :</strong> Couverture des dommages corporels et matériels causés aux tiers, conformément à la législation en vigueur ;</li>
                        <li><strong>Dommages collision :</strong> Réparation des dommages subis par le véhicule assuré en cas de collision ;</li>
                        <li><strong>Incendie et vol :</strong> Indemnisation en cas d'incendie total ou partiel, ainsi qu'en cas de vol du véhicule ;</li>
                        <li><strong>Bris de glace :</strong> Remplacement ou réparation des vitres, pare-brise et glaces latérales ;</li>
                        <li><strong>Assistance :</strong> Dépannage, remorquage et prise en charge en cas de panne ou d'accident.</li>
                    </ul>
                </div>
                <div class="clause">
                    <div class="clause-title">Article 7.3 — Obligations spécifiques</div>
                    <p>L'assuré s'engage à :</p>
                    <ul class="ctr-list">
                        <li>Maintenir le véhicule en bon état de fonctionnement et effectuer les révisions périodiques ;</li>
                        <li>Informer l'assureur de tout changement de conducteur principal ;</li>
                        <li>Ne pas prêter le véhicule à une personne non titulaire d'un permis de conduire valide ;</li>
                        <li>Déclarer tout sinistre dans le délai contractuel prévu à l'article 5.</li>
                    </ul>
                </div>

            <?php elseif ($typeAssurance === 'habitation'): ?>
                <div class="clause">
                    <div class="clause-title">Article 7.1 — Biens assurés</div>
                    <p>Le présent contrat couvre l'habitation désignée par l'assuré lors de la souscription, incluant les murs, le contenu mobilier et les biens personnels, conformément aux informations fournies dans le formulaire de devis.</p>
                </div>
                <div class="clause">
                    <div class="clause-title">Article 7.2 — Garanties habitation</div>
                    <p>L'offre <strong><?= cE($offre) ?></strong> comprend les garanties suivantes :</p>
                    <ul class="ctr-list">
                        <li><strong>Incendie et événements assimilés :</strong> Couverture des dommages causés par le feu, la foudre, l'explosion ;</li>
                        <li><strong>Dégâts des eaux :</strong> Indemnisation des dégâts causés par les fuites, inondations et infiltrations ;</li>
                        <li><strong>Vol et vandalisme :</strong> Couverture du vol de biens personnels et des dommages liés au vandalisme ;</li>
                        <li><strong>Responsabilité civile :</strong> Protection contre les dommages causés aux voisins ou aux tiers ;</li>
                        <li><strong>Tempête et catastrophes naturelles :</strong> Couverture étendue aux événements climatiques.</li>
                    </ul>
                </div>
                <div class="clause">
                    <div class="clause-title">Article 7.3 — Obligations spécifiques</div>
                    <p>L'assuré s'engage à :</p>
                    <ul class="ctr-list">
                        <li>Entretien régulier des installations (électricité, plomberie, gaz) ;</li>
                        <li>Maintenir les dispositifs de sécurité (serrures, alarmes) en bon état de fonctionnement ;</li>
                        <li>Déclarer tout changement d'usage du logement ;</li>
                        <li>Informer l'assureur en cas de travaux importants modifiant la valeur du bien.</li>
                    </ul>
                </div>

            <?php elseif ($typeAssurance === 'sante'): ?>
                <div class="clause">
                    <div class="clause-title">Article 7.1 — Personnes couvertes</div>
                    <p>Le présent contrat couvre l'assuré désigné ainsi que, le cas échéant, les personnes mentionnées dans les conditions particulières (conjoint, enfants à charge), sous réserve du respect des conditions d'âge et de lien de famille.</p>
                </div>
                <div class="clause">
                    <div class="clause-title">Article 7.2 — Garanties santé</div>
                    <p>L'offre <strong><?= cE($offre) ?></strong> comprend les garanties suivantes :</p>
                    <ul class="ctr-list">
                        <li><strong>Frais médicaux et pharmaceutiques :</strong> Prise en charge des consultations, médicaments et actes médicaux ;</li>
                        <li><strong>Hospitalisation :</strong> Couverture des frais d'hospitalisation, chirurgie et soins intensifs ;</li>
                        <li><strong>Maternité :</strong> Prise en charge des frais liés à la grossesse et à l'accouchement ;</li>
                        <li><strong>Optique et dentaire :</strong> Remboursement partiel des soins dentaires et des équipements optiques ;</li>
                        <li><strong>Prévention et bien-être :</strong> Bilans de santé annuels et programmes de prévention.</li>
                    </ul>
                </div>
                <div class="clause">
                    <div class="clause-title">Article 7.3 — Délais de carence</div>
                    <p>Sauf dispositions contraires, les garanties santé sont soumises aux délais de carence suivants :</p>
                    <ul class="ctr-list">
                        <li><strong>Maladies et hospitalisation :</strong> trente (30) jours à compter de la date d'effet ;</li>
                        <li><strong>Maternité :</strong> neuf (9) mois à compter de la date d'effet ;</li>
                        <li><strong>Chirurgie et soins spécialisés :</strong> soixante (60) jours à compter de la date d'effet.</li>
                    </ul>
                    <p>Les maladies préexistantes connues de l'assuré au moment de la souscription ne sont pas couvertes pendant la première année du contrat.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="page-break"></div>

        <div class="section">
            <div class="section-title"><span class="num">VI.</span> Résiliation et litiges</div>

            <div class="clause">
                <div class="clause-title">Article 8 — Résiliation du contrat</div>
                <p>Le présent contrat peut être résilié :</p>
                <ul class="ctr-list">
                    <li><strong>Par l'assuré :</strong> À l'échéance annuelle, moyennant un préavis de trente (30) jours par lettre recommandée avec accusé de réception ;</li>
                    <li><strong>Par l'assureur :</strong> En cas de non-paiement de la prime, de déclaration frauduleuse ou d'aggravation significative du risque ;</li>
                    <li><strong>D'un commun accord :</strong> Par avenant écrit signé par les deux parties ;</li>
                    <li><strong>Après sinistre :</strong> Conformément aux dispositions légales en vigueur.</li>
                </ul>
            </div>

            <div class="clause">
                <div class="clause-title">Article 9 — Médiation et arbitrage</div>
                <p>En cas de litige relatif à l'interprétation ou à l'exécution du présent contrat, les parties s'engagent à rechercher une solution amiable. À défaut d'accord dans un délai de trente (30) jours, le litige sera soumis à la juridiction compétente de Tunis, conformément à la législation tunisienne.</p>
            </div>

            <div class="clause">
                <div class="clause-title">Article 10 — Protection des données personnelles</div>
                <p>Les données personnelles collectées dans le cadre du présent contrat sont traitées conformément à la Loi organique n° 2004-63 du 27 juillet 2004 relative à la protection des données personnelles en Tunisie. L'assuré dispose d'un droit d'accès, de rectification et d'opposition sur ses données personnelles.</p>
            </div>
        </div>

        <div class="highlight-box green" style="margin-top:20px;">
            <strong>Information réglementaire :</strong> Le présent contrat est soumis à la législation tunisienne en matière d'assurances. L'assuré dispose d'un délai de rétractation de quatorze (14) jours à compter de la date de signature, conformément aux dispositions légales en vigueur. Passé ce délai, le contrat est considéré comme définitivement accepté par les deux parties.
        </div>

        <div class="signature-section">
            <div class="section-title"><span class="num">VII.</span> Signatures</div>
            <p style="font-size:9pt;color:#666;margin-bottom:10px;">Fait en deux (2) exemplaires originaux à Tunis, le <?= $todayLong ?>.</p>

            <div class="signature-grid">
                <div class="signature-col">
                    <div class="signature-label">L'assureur — PROTEX Assurances S.A.</div>
                    <div class="signature-line">Signature et cachet de la société</div>
                </div>
                <div class="signature-col">
                    <div class="signature-label">L'assuré — <?= cE($client) ?></div>
                    <div class="signature-line">Signature précédée de la mention « Lu et approuvé »</div>
                </div>
            </div>
        </div>

        <div class="mention-legale">
            <strong>Mentions légales :</strong> PROTEX Assurances S.A. — Société anonyme au capital de 5 000 000 TND — RC n° B 25689 2010 — Agrément Ministère des Finances n° MF 0814567V — Code Fiscal : 0814567V — Siège social : Les Berges du Lac, 1053 Tunis, Tunisie. Ce document constitue un contrat d'assurance conforme à la réglementation en vigueur en République Tunisienne. Toute reproduction, même partielle, est interdite sans autorisation écrite préalable de PROTEX Assurances S.A.
        </div>

        <div class="footer">
            PROTEX Assurance S.A. — Contrat n° <?= cE($ref) ?> — Document généré le <?= $today ?> — Page 1/1
        </div>
    </div>

</div>

<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        var btn = document.querySelector('.no-print');
        if (btn && !/PrintPreview/i.test(navigator.userAgent)) {
            window.print();
        }
    }, 500);
});
</script>

</body>
</html>
