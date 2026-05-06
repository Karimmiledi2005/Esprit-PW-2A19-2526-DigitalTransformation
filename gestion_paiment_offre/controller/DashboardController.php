<?php
/**
 * controller/DashboardController.php
 * Dashboard unifié BackOffice — KPIs des 3 modules : Devis, Offres, Paiements
 */

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { header('Location: ../view/BackOffice/connexion.html'); exit; }

include(__DIR__ . '/../config.php');

class DashboardController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function index(): void
    {
        $kpi = $this->getKPIs();
        $recentDevis = $this->getRecentDevis(5);
        $recentPaiements = $this->getRecentPaiements(5);
        $offresPerf = $this->getOffresPerformance();
        $monthlyTrend = $this->getMonthlyTrend();

        require_once __DIR__ . '/../view/BackOffice/dashboard.php';
    }

    public function diagnostic(): void
    {
        header('Content-Type: application/json');
        try {
            $kpi = $this->getKPIs();
            $recentDevis = $this->getRecentDevis(5);
            $offresPerf = $this->getOffresPerformance();
            $monthlyTrend = $this->getMonthlyTrend();
            $result = $this->getDiagnostic($kpi, $recentDevis, $offresPerf, $monthlyTrend);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function getKPIs(): array
    {
        try {
            $devis = $this->db->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(statut = 'en_attente') AS en_attente,
                    SUM(statut = 'accepte') AS acceptes,
                    SUM(statut = 'refuse') AS refuses
                FROM devis
            ")->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $devis = ['total' => 0, 'en_attente' => 0, 'acceptes' => 0, 'refuses' => 0];
        }

        try {
            $offres = $this->db->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(statut = 'active') AS actives,
                    COUNT(DISTINCT type_offre) AS types
                FROM offre
            ")->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $offres = ['total' => 0, 'actives' => 0, 'types' => 0];
        }

        try {
            $paiements = $this->db->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(statut = 'en_attente') AS en_attente,
                    SUM(statut = 'valide') AS valides,
                    SUM(CASE WHEN statut = 'valide' THEN montant ELSE 0 END) AS revenus,
                    SUM(CASE WHEN statut = 'refuse' THEN montant ELSE 0 END) AS refuses_montant
                FROM paiement
            ")->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $paiements = ['total' => 0, 'en_attente' => 0, 'valides' => 0, 'revenus' => 0, 'refuses_montant' => 0];
        }

        try {
            $conversion = $this->db->query("
                SELECT
                    COUNT(DISTINCT CASE WHEN d.statut = 'accepte' THEN d.id_devis END) AS acceptes,
                    COUNT(DISTINCT CASE WHEN c.id_contrat IS NOT NULL THEN d.id_devis END) AS convertis
                FROM devis d
                LEFT JOIN contrat c ON c.id_devis = d.id_devis
                WHERE d.statut = 'accepte'
            ")->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $conversion = ['acceptes' => 0, 'convertis' => 0];
        }

        $totalDevis = (int)$devis['total'];
        $acceptes = (int)$devis['acceptes'];
        $tauxAcceptation = $totalDevis > 0 ? round(($acceptes / $totalDevis) * 100, 1) : 0;

        $devisAcceptes = (int)$conversion['acceptes'];
        $devisConvertis = (int)$conversion['convertis'];
        $tauxConversion = $devisAcceptes > 0 ? round(($devisConvertis / $devisAcceptes) * 100, 1) : 0;

        $revenus = (float)($paiements['revenus'] ?? 0);

        return [
            'devis_total'         => (int)$devis['total'],
            'devis_en_attente'    => (int)$devis['en_attente'],
            'devis_acceptes'      => $acceptes,
            'devis_refuses'       => (int)$devis['refuses'],
            'taux_acceptation'    => $tauxAcceptation,
            'offres_total'        => (int)$offres['total'],
            'offres_actives'      => (int)$offres['actives'],
            'offres_types'        => (int)$offres['types'],
            'paiements_total'     => (int)$paiements['total'],
            'paiements_en_attente'=> (int)$paiements['en_attente'],
            'paiements_valides'   => (int)$paiements['valides'],
            'revenus'             => $revenus,
            'refuses_montant'     => (float)($paiements['refuses_montant'] ?? 0),
            'devis_convertis'     => $devisConvertis,
            'taux_conversion'     => $tauxConversion,
            'devis_sans_paiement' => $devisAcceptes - $devisConvertis,
        ];
    }

    private function getRecentDevis(int $limit = 5): array
    {
        try {
            return $this->db->query("
                SELECT d.*, o.nom_offre
                FROM devis d
                LEFT JOIN offre o ON d.id_offre = o.id_offre
                ORDER BY d.date_demande DESC
                LIMIT $limit
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getRecentPaiements(int $limit = 5): array
    {
        try {
            return $this->db->query("
                SELECT p.*, o.nom_offre,
                       d.nom AS client_nom, d.prenom AS client_prenom, d.id_devis
                FROM paiement p
                LEFT JOIN offre o ON p.id_offre = o.id_offre
                LEFT JOIN devis d ON p.id_devis = d.id_devis
                ORDER BY p.date_paiement DESC
                LIMIT $limit
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getOffresPerformance(): array
    {
        try {
            return $this->db->query("
                SELECT o.nom_offre, o.type_offre, o.statut,
                       (SELECT COUNT(*) FROM devis d WHERE d.id_offre = o.id_offre) AS nb_devis,
                       (SELECT COUNT(*) FROM paiement p WHERE p.id_offre = o.id_offre AND p.statut = 'valide') AS nb_paiements,
                       (SELECT COALESCE(SUM(p.montant), 0) FROM paiement p WHERE p.id_offre = o.id_offre AND p.statut = 'valide') AS revenus
                FROM offre o
                ORDER BY revenus DESC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getMonthlyTrend(): array
    {
        try {
            return $this->db->query("
                SELECT
                    DATE_FORMAT(date_paiement, '%Y-%m') AS mois,
                    COUNT(*) AS nb_paiements,
                    SUM(CASE WHEN statut = 'valide' THEN montant ELSE 0 END) AS revenus,
                    SUM(CASE WHEN statut = 'valide' THEN 1 ELSE 0 END) AS valides
                FROM paiement
                WHERE date_paiement >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY mois
                ORDER BY mois ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getDiagnostic(array $kpi, array $recentDevis, array $offresPerf, array $monthlyTrend): array
    {
        $alerts = [];
        $recommendations = [];
        $trends = [];
        $score = 100;

        $totalDevis = $kpi['devis_total'];
        $enAttente = $kpi['devis_en_attente'];
        $acceptes = $kpi['devis_acceptes'];
        $refuses = $kpi['devis_refuses'];
        $tauxAcceptation = $kpi['taux_acceptation'];
        $tauxConversion = $kpi['taux_conversion'];
        $devisSansContrat = $kpi['devis_sans_paiement'];
        $paiementsTotal = $kpi['paiements_total'];
        $paiementsEnAttente = $kpi['paiements_en_attente'];
        $revenus = $kpi['revenus'];

        if ($totalDevis > 0) {
            $ratioAttente = ($enAttente / $totalDevis) * 100;
            if ($ratioAttente > 50) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'hourglass-split',
                    'title' => 'Trop de devis en attente',
                    'message' => round($ratioAttente) . "% des devis ($enAttente sur $totalDevis) sont encore en attente. Cela indique un retard de traitement.",
                    'impact' => -15
                ];
                $recommendations[] = "Priorisez le traitement des $enAttente devis en attente. Envoyez des relances automatiques aux clients après 48h sans réponse.";
                $score -= 15;
            } elseif ($ratioAttente > 30) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'clock',
                    'title' => 'Devis en attente modéré',
                    'message' => round($ratioAttente) . "% des devis sont en attente. Surveillez les délais de réponse.",
                    'impact' => -5
                ];
                $score -= 5;
            }
        }

        if ($tauxAcceptation < 30 && $totalDevis > 5) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'graph-down-arrow',
                'title' => 'Taux d\'acceptation faible',
                'message' => "Seulement $tauxAcceptation% des devis sont acceptés. Les offres peuvent être mal calibrées ou trop chères.",
                'impact' => -20
            ];
            $recommendations[] = "Révisez vos offres : comparez les prix avec le marché, ajoutez des options intermédiaires, ou améliorez la communication sur la valeur ajoutée.";
            $score -= 20;
        }

        if ($devisSansContrat > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'title' => 'Devis acceptés sans contrat',
                'message' => "$devisSansContrat devis acceptés n'ont pas encore été convertis en contrat. Vous perdez des revenus potentiels.",
                'impact' => -10
            ];
            $recommendations[] = "Convertissez immédiatement les $devisSansContrat devis acceptés en contrats. Chaque jour sans contrat est un risque de perdre le client.";
            $score -= 10;
        }

        if ($tauxConversion < 50 && $acceptes > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'funnel',
                'title' => 'Conversion devis → contrat insuffisante',
                'message' => "Le taux de conversion est de $tauxConversion%. Plus de la moitié des devis acceptés ne deviennent pas des contrats.",
                'impact' => -15
            ];
            $recommendations[] = "Mettez en place un processus de suivi post-acceptation : rappel téléphonique sous 24h, email de confirmation avec lien de paiement, relance J+3.";
            $score -= 15;
        } elseif ($tauxConversion >= 80) {
            $trends[] = [
                'type' => 'success',
                'icon' => 'graph-up-arrow',
                'title' => 'Excellente conversion',
                'message' => "Taux de conversion de $tauxConversion% — votre processus de vente fonctionne très bien."
            ];
            $score += 5;
        }

        if ($paiementsEnAttente > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'credit-card',
                'title' => 'Paiements en attente',
                'message' => "$paiementsEnAttente paiement(s) sont en attente de validation pour un montant estimé.",
                'impact' => 0
            ];
            $recommendations[] = "Vérifiez les paiements en attente et validez-les ou contactez les clients pour finaliser.";
        }

        if (count($offresPerf) > 0) {
            $offreFaible = null;
            $offreForte = null;
            foreach ($offresPerf as $o) {
                if ((int)$o['nb_devis'] === 0 && $offreFaible === null) $offreFaible = $o;
                if ($offreForte === null) $offreForte = $o;
            }
            if ($offreFaible) {
                $trends[] = [
                    'type' => 'info',
                    'icon' => 'eye-slash',
                    'title' => 'Offre peu performante',
                    'message' => "L'offre '{$offreFaible['nom_offre']}' n'a généré aucun devis. Envisagez de la modifier ou la retirer."
                ];
            }
            if ($offreForte && (int)$offreForte['nb_devis'] > 0) {
                $trends[] = [
                    'type' => 'success',
                    'icon' => 'trophy',
                    'title' => 'Offre la plus populaire',
                    'message' => "L'offre '{$offreForte['nom_offre']}' est la plus demandée avec {$offreForte['nb_devis']} devis."
                ];
            }
        }

        if (count($monthlyTrend) >= 2) {
            $last = end($monthlyTrend);
            $prev = prev($monthlyTrend);
            if ($prev['revenus'] > 0) {
                $croissance = round((($last['revenus'] - $prev['revenus']) / $prev['revenus']) * 100, 1);
                if ($croissance > 0) {
                    $trends[] = [
                        'type' => 'success',
                        'icon' => 'trending-up',
                        'title' => 'Croissance des revenus',
                        'message' => "Les revenus ont augmenté de $croissance% par rapport au mois précédent."
                    ];
                    $score += 10;
                } elseif ($croissance < 0) {
                    $trends[] = [
                        'type' => 'warning',
                        'icon' => 'trending-down',
                        'title' => 'Baisse des revenus',
                        'message' => "Les revenus ont diminué de " . abs($croissance) . "% par rapport au mois précédent."
                    ];
                    $score -= 10;
                }
            }
        }

        if ($refuses > $acceptes && $totalDevis > 5) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'x-circle',
                'title' => 'Plus de refus que d\'acceptations',
                'message' => "Il y a $refuses refus contre $acceptes acceptations. Analysez les raisons de refus pour améliorer vos offres.",
                'impact' => -20
            ];
            $score -= 20;
        }

        if ($score > 100) $score = 100;
        if ($score < 0) $score = 0;

        if ($score >= 80) {
            $recommendations[] = "Votre activité est en bonne santé. Continuez à optimiser le processus de conversion et explorez de nouvelles offres.";
        } elseif ($score >= 50) {
            $recommendations[] = "Des améliorations sont nécessaires. Concentrez-vous sur les alertes prioritaires ci-dessus pour augmenter votre score.";
        } else {
            $recommendations[] = "Attention : votre activité nécessite une attention urgente. Traitez les alertes critiques en priorité.";
        }

        $scoreLabel = $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Bon' : ($score >= 40 ? 'Moyen' : 'Critique'));
        $scoreColor = $score >= 80 ? '#90f1bc' : ($score >= 60 ? '#ffd66e' : ($score >= 40 ? '#ffd6a0' : '#ff9cab'));

        return [
            'alerts' => $alerts,
            'recommendations' => $recommendations,
            'trends' => $trends,
            'score' => $score,
            'score_label' => $scoreLabel,
            'score_color' => $scoreColor,
            'score_emoji' => $score >= 80 ? '🟢' : ($score >= 60 ? '🟡' : ($score >= 40 ? '🟠' : '🔴')),
        ];
    }
}

// ═══════════════════════════════════════════════════════════════
// ROUTEUR
// ═══════════════════════════════════════════════════════════════

$controller = new DashboardController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'diagnostic': $controller->diagnostic(); break;
    default:           $controller->index();     break;
}
