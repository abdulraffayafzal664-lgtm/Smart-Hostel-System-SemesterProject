<?php
// Abstract parent class (matches the class diagram: User -> Student / Warden / MessStaff)
abstract class User {
    public function __construct(protected array $row = []) {}
    public function name(): string { return $this->row["name"] ?? ""; }
    abstract public function role(): string;          // each child must implement these
    abstract public function dashboard(): string;
    abstract public function menu(): array;           // page => label
}
class Student extends User {
    public function role(): string { return "Student"; }
    public function dashboard(): string { return "student_dashboard.php"; }
    public function menu(): array {
        return ["student_dashboard.php" => "My room", "student_meals.php" => "Meals",
                "student_bill.php" => "Bill", "student_complaints.php" => "Complaints"];
    }
}
class Warden extends User {
    public function role(): string { return "Warden"; }
    public function dashboard(): string { return "warden_rooms.php"; }
    public function menu(): array {
        return ["warden_rooms.php" => "Rooms", "warden_students.php" => "Students", "warden_complaints.php" => "Complaints",
                "warden_reports.php" => "Reports", "warden_settings.php" => "Meal settings"];
    }
}
class MessStaff extends User {
    public function role(): string { return "MessStaff"; }
    public function dashboard(): string { return "mess_counts.php"; }
    public function menu(): array { return ["mess_counts.php" => "Meal count"]; }
}

// DESIGN PATTERN 2: FACTORY
// Callers never write "new Student" / "new Warden": they ask the factory, which picks the right class from the role.
class UserFactory {
    public static function create(array $row): User {
        return match ($row["role"] ?? "") {
            "Student"   => new Student($row),
            "Warden"    => new Warden($row),
            "MessStaff" => new MessStaff($row),
            default     => throw new InvalidArgumentException("Unknown role"),
        };
    }
}
