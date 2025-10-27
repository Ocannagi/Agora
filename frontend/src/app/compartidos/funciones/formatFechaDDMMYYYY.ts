export function formatFechaDDMMYYYY(value?: string | Date | null): string {
    if (!value) return '';
    // Evitar desfases: si viene 'YYYY-MM-DD' o 'YYYY-MM-DDTHH:mm...'
    if (typeof value === 'string') {
      const m = value.match(/^(\d{4})-(\d{2})-(\d{2})/);
      if (m) {
        const [, yyyy, mm, dd] = m;
        return `${dd}/${mm}/${yyyy}`;
      }
    }
    const d = new Date(value as any);
    if (Number.isNaN(d.getTime())) return '';
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yyyy = String(d.getFullYear());
    return `${dd}/${mm}/${yyyy}`;
  }