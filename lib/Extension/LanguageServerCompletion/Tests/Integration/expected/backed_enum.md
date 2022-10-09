enum Foobar: string {
    public static function cases(): BackedEnumCase[]
    public static function from(int|string $value): static(Foobar)
    public static function tryFrom(int|string $value): static(Foobar)|null
    case FOOBAR = "bar";
}
