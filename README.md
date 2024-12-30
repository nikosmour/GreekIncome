# Εγχειρίδιο Ελέγχου Εισοδημάτων

## Περιγραφή
Επαλήθευσή τιμών στο ελληνικό εκκαθαριστικό αξιοποιώντας την πλατφόρμα
[Εγκυρότητα εκκαθαριστικού δήλωσης φορολογίας εισοδήματος](https://www.aade.gr/egkyrotita-ekkatharistikoy-dilosis-forologias-eisodimatos)
## Υποχρεωτικά πεδία

| Κωδικός        | Περιγραφή                   | Τύπος         | Περιορισμοί       |
|----------------|-----------------------------|---------------|-------------------|
| **AFM**        | Α.Φ.Μ. υπόχρεου ή συζύγου   | int \| string | 9 χαρακτήρες      |
| **AR_DILOSIS** | Αριθμός φορολογικής δήλωσης | int \| string | θετικός ακέραιος  |
| **YEAR**       | Έτος δήλωσης                | int \| string | 2003 +            |

---

## Πεδία ελέγχου

Για κάθε άτομο, μπορείτε να ελέγξετε τα παρακάτω πεδία:

| Κωδικός           | Περιγραφή                               | Τύπος         | Περιορισμοί       |
|-------------------|-----------------------------------------|---------------|-------------------|
| **AYT_FOR_EISOD** | Αυτοτ. Φορολ. Ποσά κτλ.                 | int \| string | θετικός ακέραιος  |
| **DHLWTHEN**      | Δηλωθέν Εισόδημα                        | int \| string | θετικός ακέραιος  |
| **EISODHMA**      | Σύνολο Εισοδήματος                      | int \| string | θετικός ακέραιος  |
| **EISODHMA_A**    | Ακίνητη Περιουσία                       | int \| string | θετικός ακέραιος  |
| **EISODHMA_C**    | Μερίσματα - Τόκοι - Δικαιώματα          | int \| string | θετικός ακέραιος  |
| **EISODHMA_E**    | Αγροτική Επιχ. Δραστηριότητα            | int \| string | θετικός ακέραιος  |
| **EISODHMA_D**    | Επιχειρηματική Δραστηριότητα            | int \| string | θετικός ακέραιος  |
| **EISODHMA_ST**   | Μισθωτή Εργασία - Συντάξεις             | int \| string | θετικός ακέραιος  |
| **EISODHMA_Z**    | Υπεραξία Μεταβ/σης Κεφαλαίου            | int \| string | θετικός ακέραιος  |
| **EISODHMA_AL**   | Εισόδημα Ναυτ/Κυβερν/Μηχαν Αεροσκαφών   | int \| string | θετικός ακέραιος  |
| **EPIDOMA_OAED**  | Επίδομα Ανεργίας                        | int \| string | θετικός ακέραιος  |
| **TZIROS**        | Ακαθ. Έσοδα Επιχειρ. Δραστ.             | int \| string | θετικός ακέραιος  |
| **BOYL_APOZ**     | Ακαθ. Έσοδα Αγροτ. Επιχ. Δραστ.         | int \| string | θετικός ακέραιος  |

> **Προσοχή**:
> Τα πεδία δεν είναι υποχρεωτικά και μπορείτε να στέλνετε είτε strings είτε νούμερα. Ωστόσο,
> - Τουλάχιστον ένα πεδίο ελέγχου πρέπει να οριστεί.
> - Εφόσον δηλωθεί το πεδίο η τιμή πρέπει να είναι θετικός ακέραιος γιατί:
>   - Δεν είναι εφικτό να επαληθευτεί μηδενική τιμή από την πλατφόρμα οπότε
      δεν επιτρέπετε να στείλετε μηδενικές τιμές
>   - Δεν είναι εφικτό να επαληθευτεί δεκαδικό μέρος στην τιμή από την πλατφόρμα οπότε
      επιτρέπετε να στέλνετε μόνο το ακέραιο μέρος του αριθμού (στρογγυλοποίηση προς τα κάτω).

---

## Τρόπος Ελέγχου

> Ο έλεγχος γίνεται μέσω δημιουργίας στιγμιότυπου της κλάσης `VerifyIncomeController.php` με είσοδο ένα πίνακα που περιλαμβάνει:
> 1. **Υποχρεωτικά κλειδιά**: `AFM`, `AR_DILOSIS`, `YEAR`.
> 2. **Προαιρετικά πεδία**:
>    1. `ypoxreos` και `sizigos`, που περιέχουν arrays με τα κλειδιά ελέγχου και τις αντίστοιχες τιμές για έλεγχο.
>    2. κλειδιά ελέγχου με τιμή
>       1. για ελέγχο έαν είναι για υπόχρεο μόνο
>       2. πίνακας με κλειδί (`0` για υπόχρεο, `1` για σύζυγο) και τιμές για ελέγχο
> 3.  Τουλάχιστον ένα πεδίο για κλειδί στην τιμή του πεδίου `ypoxreos` ή του πεδίου `sizigos`
> 4.  Σε περίπτωση αλληλεπικάλυψης τιμής σε αυτόνομο κλειδί με τον `ypoxreos` ή `sizigos` υπερισχύει του αυτόνομου


### Διαφορετικός τρόπος έκφρασης δεδομένων εισόδου

```php
<?php

$data = [
    //...
    'ypoxreos' => [
        'EISODHMA_A' => 10000,
        'EISODHMA_C' => 2000,
        'EISODHMA_E' => 3000,
    ],
    'sizigos' => [
        'EISODHMA_E' => 1500,
        'AYT_FOR_EISOD' => 1500,
    ],
    'EISODHMA_A' => 1800,
    //...
];
// equals to
$data = [
    //...
    'ypoxreos' => [ // μόνο υπόχρεος
        'EISODHMA_A' => 1800, // υπερισχύει η τιμή του αυτόνομου
    ],
    'EISODHMA_C' => 2000, // ή [2000] ή [0=>2000] μόνο υπόχρεος
    'EISODHMA_E' => [3000,1500], // και τον δύο
    'AYT_FOR_EISOD'=> [1=>1500] // μόνο συζύγου
    //...
];

?>

```

### Παράδειγμα Κώδικα

```php
<?php
use GreekIncome\Services\VerifyIncomeService;

// Δεδομένα για έλεγχο
$data = [
    'AFM' => '123456789', // Υποχρεωτικά string εάν ξεκινάει με 0
    'AR_DILOSIS' => '987654321',
    'YEAR' => 2024,
    'ypoxreos' => [
        'EISODHMA_A' => 10000,
        'EISODHMA_C' => 2000,
    ],
    'sizigos' => [
        'EISODHMA_Α' => 3000,
        'EISODHMA_D' => 1500,
    ],
];

// Εκτέλεση της υπηρεσίας ελέγχου
$result = (new VerifyIncomeService([$data]))->verify();
dd($result);
?>

```

### Αναμενόμενα Αποτελέσματα

Το αποτέλεσμα της επεξεργασίας είναι ένα JSON object που περιέχει τα εξής πεδία:
- **success**: Δείχνει αν η επεξεργασία ήταν επιτυχής (`true`, `false`, ή `null`).
- **input**: Τα αρχικά δεδομένα που δόθηκαν από τον χρήστη.
- **output**: Τα δεδομένα που παράγονται από την επεξεργασία.

Παρακάτω παρουσιάζονται διαφορετικά παραδείγματα εξόδου ανάλογα με την τιμή του `success`.

#### Παράδειγμα Επιτυχίας (success: true)
```json
{
    "success": true,
    "input": {
        "AFM": "123456789",
        "AR_DILOSIS": "987654321",
        "YEAR": "2024",
        "EISODHMA": 20000,
        "DHLWTHEN": [1200]
    },
    "output": {
    "ypoxreos": {
          "EISODHMA": true,
          "DHLWTHEN": true
    },
    "sizigos": []
    }
}
```

#### Παράδειγμα Αποτυχίας (success: false)
```json
{
    "success": false,
    "input": {
        "AFM": "123456789",
        "AR_DILOSIS": "987654321",
        "YEAR": "2024",
        "ypoxreos": {
            "EISODHMA": 20000,
            "DHLWTHEN": 1200
        }
    },
    "output": {
        "ypoxreos": {
          "EISODHMA": false,
          "DHLWTHEN": false
        },
        "sizigos": []
    }
}
```

#### Παράδειγμα Μερικής Επιτυχίας (success: null)
```json
{
    "success": null,
    "input": {
        "AFM": "123456789",
        "AR_DILOSIS": "987654321",
        "YEAR": "2024",
        "ypoxreos": {
            "EISODHMA": 20000
        },
        "DHLWTHEN": [1200]
    },
    "output": {
        "ypoxreos": {
            "EISODHMA": true,
            "DHLWTHEN": false
        },
        "sizigos": []
    }
}
```
## Errors

| type                     | when                                       | 
|--------------------------|--------------------------------------------|
| typeError                | Invalid value type                         |
| UnexpectedValueException | Invalid value                              |
| InvalidArgumentException | Invalid key                                |
| ErrorException           | Δεν έχουν οριστεί όλα τα υποχρεωτικά πεδία |
| LengthException          | Λάθος πλήθος χαρακτήρων μόνο για το ΑΦΜ    |